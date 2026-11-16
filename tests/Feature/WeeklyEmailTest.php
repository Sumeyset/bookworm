<?php

use App\Mail\WeeklyDigest;
use App\Models\Book;
use App\Models\ReadingSession;
use App\Models\User;
use App\Support\Digest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

test('the weekly email tells you what friends finished and can be unsubscribed from', function () {
    Mail::fake();
    $me = User::factory()->create();
    $friend = User::factory()->create(['name' => 'Fiona Friend']);
    $quiet = User::factory()->create();
    $me->following()->attach($friend->id);
    $book = Book::create(['ol_id' => 'OL-WEEK', 'title' => 'Weekly Wonders']);
    $friend->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'read_at' => now()->subDay()]);
    ReadingSession::log($me->id, $book->id, 30);
    ReadingSession::log($quiet->id, $book->id, 0);
    ReadingSession::where('user_id', $quiet->id)->update(['read_on' => now()->subDays(20)->toDateString()]);

    expect(Digest::sendAll())->toBe(1);
    Mail::assertSent(WeeklyDigest::class, fn ($mail) => $mail->hasTo($me->email) && in_array('Weekly Wonders', array_column($mail->digest['friends'], 'title')));
    expect(Digest::sendAll())->toBe(0); // not twice in a week

    $html = (new WeeklyDigest($me, Digest::for($me)))->render();
    expect($html)->toContain('Fiona Friend')->toContain('Unsubscribe');

    // Unsubscribe needs a signed link.
    $this->get(route('email.unsubscribe', $me))->assertForbidden();
    $this->get(URL::signedRoute('email.unsubscribe', ['user' => $me->id]))->assertOk()->assertSee('unsubscribed');
    expect($me->fresh()->weekly_email)->toBeFalse();
});
