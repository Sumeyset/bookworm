<?php

use App\Models\Post;
use App\Models\Report;
use App\Models\User;

test('blocking hides readers from each other and ends follows', function () {
    $me = User::factory()->create();
    $troll = User::factory()->create(['name' => 'Troll Person', 'username' => 'trollperson']);
    $me->following()->attach($troll->id);
    $troll->following()->attach($me->id);
    $post = Post::create(['user_id' => $troll->id, 'type' => 'text', 'body' => 'A post from the troll']);

    $this->actingAs($me)->post(route('users.block', $troll->username))->assertRedirect();
    expect($me->isFollowing($troll))->toBeFalse()->and($troll->isFollowing($me))->toBeFalse();

    // Neither can see the other's profile or posts.
    $this->actingAs($me)->get(route('users.show', $troll->username))->assertNotFound();
    $this->actingAs($troll)->get(route('users.show', $me->username))->assertNotFound();
    $this->actingAs($me)->get(route('posts.show', $post))->assertNotFound();
    $this->actingAs($me)->get(route('feed.index', ['scope' => 'everyone']))->assertDontSee('A post from the troll');
    $this->actingAs($me)->getJson(route('users.suggest', ['q' => 'troll']))->assertExactJson([]);

    // No following, messaging or notifications across a block.
    $this->actingAs($troll)->post(route('users.follow', $me->username))->assertNotFound();
    $this->actingAs($troll)->get(route('messages.start', $me->username))->assertSessionHasErrors('message');
    \App\Notifications\BookwormNotification::send($me, 'like', 'Troll liked your post', '/', $troll);
    expect($me->notifications()->count())->toBe(0);

    // Unblock.
    $this->actingAs($me)->delete(route('users.unblock', $troll->username));
    $me->forgetBlockCache();
    $this->actingAs($me)->get(route('users.show', $troll->username))->assertOk();
});

test('private accounts approve followers before showing their reading life', function () {
    $private = User::factory()->create(['is_private' => true]);
    $fan = User::factory()->create();

    $this->actingAs($fan)->get(route('users.show', $private->username))->assertOk()->assertSee('This account is private')->assertDontSee('id="bookshelf"', false);
    $this->actingAs($fan)->post(route('users.follow', $private->username))->assertSessionHas('status');
    expect($fan->isFollowing($private))->toBeFalse()
        ->and($private->followRequests()->count())->toBe(1);
    $this->actingAs($fan)->get(route('users.show', $private->username))->assertSee('Requested · Cancel');

    $this->actingAs($private)->get(route('follow-requests.index'))->assertOk()->assertSee($fan->name);
    $this->actingAs($private)->post(route('follow-requests.approve', $fan->username))->assertRedirect();
    expect($fan->fresh()->isFollowing($private))->toBeTrue();
    $this->actingAs($fan)->get(route('users.show', $private->username))->assertOk()->assertDontSee('This account is private');
});

test('going public lets everyone waiting in, and hiding activity stops friend notes', function () {
    $me = User::factory()->create(['is_private' => true]);
    $waiting = User::factory()->create();
    $waiting->sentFollowRequests()->attach($me->id);

    $this->actingAs($me)->patch(route('settings.privacy'), ['hide_activity' => '1'])->assertRedirect();
    expect($waiting->isFollowing($me))->toBeTrue()->and($me->fresh()->hide_activity)->toBeTrue();

    $book = \App\Models\Book::create(['ol_id' => 'OL-HIDE', 'title' => 'Hidden Book']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading']);
    expect($waiting->notifications()->where('data->kind', 'reading')->count())->toBe(0);
});

test('readers report content, admins review it and can suspend', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $reader = User::factory()->create();
    $spammer = User::factory()->create();
    $post = Post::create(['user_id' => $spammer->id, 'type' => 'text', 'body' => 'Buy cheap followers!!!']);

    $this->actingAs($reader)->post(route('reports.store'), ['type' => 'post', 'id' => $post->id, 'reason' => 'spam'])->assertSessionHas('status');
    $this->actingAs($reader)->post(route('reports.store'), ['type' => 'post', 'id' => $post->id, 'reason' => 'spam']); // once is enough
    expect(Report::count())->toBe(1)
        ->and(Report::first()->excerpt)->toContain('Buy cheap followers')
        ->and($admin->notifications()->where('data->kind', 'report')->count())->toBe(1);

    $this->actingAs($reader)->get(route('admin.reports'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.reports'))->assertOk()->assertSee('Buy cheap followers');

    $report = Report::first();
    $this->actingAs($admin)->post(route('admin.reports.resolve', $report), ['action' => 'remove'])->assertRedirect();
    expect(Post::find($post->id))->toBeNull()->and($report->fresh()->status)->toBe('actioned');

    // Suspend: the account is signed out on its next page.
    $other = Report::create(['reporter_id' => $reader->id, 'reported_user_id' => $spammer->id, 'reportable_type' => 'user', 'reportable_id' => $spammer->id, 'reason' => 'spam']);
    $this->actingAs($admin)->post(route('admin.reports.resolve', $other), ['action' => 'suspend']);
    $this->actingAs($spammer->fresh())->get(route('feed.index'))->assertRedirect(route('login'));
    $this->actingAs($reader)->get(route('users.show', $spammer->username))->assertNotFound();
});

test('you cannot report a message from a chat you are not in', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $outsider = User::factory()->create();
    $chat = \App\Models\Conversation::between($a, $b);
    $message = $chat->messages()->create(['user_id' => $a->id, 'body' => 'hello']);

    $this->actingAs($outsider)->post(route('reports.store'), ['type' => 'message', 'id' => $message->id, 'reason' => 'spam'])->assertNotFound();
    $this->actingAs($b)->post(route('reports.store'), ['type' => 'message', 'id' => $message->id, 'reason' => 'harassment'])->assertSessionHas('status');
});
