<?php

use App\Models\Book;
use App\Models\ReadingSession;
use App\Models\User;
use App\Support\Streak;
use Illuminate\Support\Carbon;

function readOn(User $user, string $day): void
{
    $book = Book::firstOrCreate(['ol_id' => 'OL-STREAK'], ['title' => 'Streak Book']);
    ReadingSession::create(['user_id' => $user->id, 'book_id' => $book->id, 'read_on' => $day, 'pages_read' => 10]);
}

test('a streak freeze covers a missed day', function () {
    Carbon::setTestNow('2026-11-20 00:05:00');
    $me = User::factory()->create(['streak_freezes' => 1]);
    readOn($me, '2026-11-16');
    readOn($me, '2026-11-17');
    readOn($me, '2026-11-18');
    // missed the 19th

    expect(Streak::current($me))->toBe(0);
    expect(Streak::applyFreezes())->toBe(1);
    expect(Streak::current($me))->toBe(4)
        ->and($me->fresh()->streak_freezes)->toBe(0)
        ->and($me->notifications()->where('data->kind', 'streak')->count())->toBe(1);

    // Nothing more to cover.
    expect(Streak::applyFreezes())->toBe(0);
    Carbon::setTestNow();
});

test('buying a freeze costs acorns and is capped', function () {
    $me = User::factory()->create(['acorns' => 1000]);
    $this->actingAs($me)->post(route('shop.freeze'))->assertSessionHasNoErrors();
    $this->actingAs($me)->post(route('shop.freeze'))->assertSessionHasNoErrors();
    $this->actingAs($me)->post(route('shop.freeze'))->assertSessionHasErrors('shop');

    expect($me->fresh()->streak_freezes)->toBe(2)->and($me->fresh()->acorns)->toBe(1000 - 2 * Streak::FREEZE_PRICE);
});

test('the reminder goes out once, at the chosen hour, only when the streak is at risk', function () {
    Carbon::setTestNow('2026-11-20 19:02:00'); // UTC
    $me = User::factory()->create(['streak_reminder' => true, 'reminder_hour' => 20, 'timezone' => 'Europe/Paris']); // 20:02 in Paris
    $lazy = User::factory()->create(['streak_reminder' => false, 'reminder_hour' => 20, 'timezone' => 'Europe/Paris']);
    foreach ([$me, $lazy] as $user) {
        readOn($user, '2026-11-19');
    }

    expect(Streak::sendReminders())->toBe(1);
    expect(Streak::sendReminders())->toBe(0); // once a day

    // Reading today means no reminder tomorrow evening for today.
    expect($me->notifications()->where('data->kind', 'streak')->count())->toBe(1);
    Carbon::setTestNow();
});

test('reminder settings save with the time zone', function () {
    $me = User::factory()->create();
    $this->actingAs($me)->patch(route('settings.reminders'), ['streak_reminder' => '1', 'reminder_hour' => 21, 'timezone' => 'Asia/Baku'])->assertRedirect();

    expect($me->fresh())->streak_reminder->toBeTrue()->reminder_hour->toBe(21)->timezone->toBe('Asia/Baku');
    $this->actingAs($me)->get(route('profile.edit'))->assertOk()->assertSee('Remind me before my streak ends');
});

test('push: devices subscribe, notifications and messages are pushed, and it is off without keys', function () {
    $me = User::factory()->create();
    $this->actingAs($me)->postJson(route('push.subscribe'), [
        'endpoint' => 'https://push.example.test/abc', 'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
    ])->assertOk();
    expect($me->fresh()->hasMany(\App\Models\PushSubscription::class)->count())->toBe(1);

    // Without VAPID keys nothing is sent and nothing breaks.
    config(['webpush.public_key' => null, 'webpush.private_key' => null]);
    \App\Notifications\BookwormNotification::send($me, 'badge', 'Hello', '/');
    expect($me->notifications()->count())->toBe(1);

    $this->actingAs($me)->deleteJson(route('push.unsubscribe'), ['endpoint' => 'https://push.example.test/abc'])->assertOk();
    expect(\App\Models\PushSubscription::count())->toBe(0);
});

test('the app manifest, service worker and offline page are served', function () {
    $this->get('/offline')->assertOk()->assertSee('You’re offline');
    expect(file_exists(public_path('sw.js')))->toBeTrue()
        ->and(json_decode(file_get_contents(public_path('manifest.webmanifest')), true)['short_name'])->toBe('Bookworm');
    $this->actingAs(User::factory()->create())->get(route('feed.index'))->assertSee('manifest.webmanifest', false);
});
