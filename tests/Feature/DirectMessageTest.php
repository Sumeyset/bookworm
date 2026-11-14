<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;

test('friends can message each other and see unread chats', function () {
    $me = User::factory()->create(['name' => 'Me Reader']);
    $friend = User::factory()->create(['name' => 'Fern Friend']);
    $friend->following()->attach($me->id); // they follow me, so my message goes straight to their inbox

    $this->actingAs($me)->get(route('messages.start', $friend->username))->assertRedirect();
    $conversation = Conversation::firstOrFail();

    $this->postJson(route('messages.store', $conversation), ['body' => 'Have you started Middlemarch?'])
        ->assertCreated()
        ->assertJsonPath('id', Message::first()->id);

    expect($friend->unreadConversationCount())->toBe(1)
        ->and($friend->messageRequestCount())->toBe(0);

    $this->actingAs($friend)->get(route('messages.index'))->assertOk()->assertSee('Me Reader')->assertSee('Have you started Middlemarch?');
    $this->get(route('messages.show', $conversation))->assertOk()->assertSee('Have you started Middlemarch?');
    expect($friend->fresh()->unreadConversationCount())->toBe(0);

    // A reply shows up for me through polling.
    $this->post(route('messages.store', $conversation), ['body' => 'Chapter 3!'])->assertRedirect(route('messages.show', $conversation));
    $this->actingAs($me)->getJson(route('messages.poll', [$conversation, 'after' => Message::first()->id]))
        ->assertOk()
        ->assertJsonPath('last_id', Message::latest('id')->first()->id)
        ->assertSee('Chapter 3!');

    // Messaging the same person again reuses the chat.
    $this->get(route('messages.start', $friend->username));
    expect(Conversation::count())->toBe(1);
});

test('messages from people you don’t follow arrive as requests', function () {
    $me = User::factory()->create();
    $stranger = User::factory()->create(['name' => 'Stranger Danger']);

    $this->actingAs($stranger)->get(route('messages.start', $me->username));
    $conversation = Conversation::firstOrFail();
    $this->post(route('messages.store', $conversation), ['body' => 'Hi! Loved your review.']);

    expect($me->messageRequestCount())->toBe(1)->and($me->unreadConversationCount())->toBe(0);
    $this->actingAs($me)->get(route('messages.index'))->assertDontSee('Stranger Danger');
    $this->get(route('messages.index', ['tab' => 'requests']))->assertSee('Stranger Danger');
    $this->get(route('messages.show', $conversation))->assertSee('wants to send you a message');

    $this->post(route('messages.accept', $conversation))->assertRedirect(route('messages.show', $conversation));
    expect($me->fresh()->messageRequestCount())->toBe(0);
    $this->get(route('messages.index'))->assertSee('Stranger Danger');
});

test('a request can be deleted, and outsiders can’t read or post in a chat', function () {
    $me = User::factory()->create();
    $stranger = User::factory()->create();
    $outsider = User::factory()->create();

    $this->actingAs($stranger)->get(route('messages.start', $me->username));
    $conversation = Conversation::firstOrFail();
    $this->post(route('messages.store', $conversation), ['body' => 'Buy my book!']);

    $this->actingAs($outsider)->get(route('messages.show', $conversation))->assertNotFound();
    $this->post(route('messages.store', $conversation), ['body' => 'Hi'])->assertNotFound();
    $this->getJson(route('messages.poll', $conversation))->assertNotFound();

    // The sender can't "delete" their own sent chat as if it were a request.
    $this->actingAs($stranger)->delete(route('messages.destroy', $conversation))->assertForbidden();

    $this->actingAs($me)->delete(route('messages.destroy', $conversation))->assertRedirect();
    expect(Conversation::count())->toBe(0)->and(Message::count())->toBe(0);

    // You can't message yourself.
    $this->get(route('messages.start', $me->username))->assertNotFound();
});

test('the send button on a post shares it into chats', function () {
    $me = User::factory()->create();
    $a = User::factory()->create(['username' => 'ana']);
    $b = User::factory()->create(['username' => 'ben']);
    $me->following()->attach([$a->id, $b->id]);
    $post = Post::create(['user_id' => $a->id, 'type' => 'post', 'body' => 'Look at my shelf']);

    $this->actingAs($me)->getJson(route('messages.contacts'))->assertOk()->assertJsonCount(2);

    $this->postJson(route('posts.send', $post), ['recipients' => ['ana', 'ben'], 'body' => 'So pretty'])
        ->assertCreated()
        ->assertJsonPath('sent', 2);

    expect(Conversation::count())->toBe(2)
        ->and(Message::whereNotNull('post_id')->count())->toBe(2)
        ->and(Message::where('body', 'So pretty')->count())->toBe(2);

    $this->actingAs($b)->get(route('messages.show', Conversation::whereHas('users', fn ($q) => $q->where('users.id', $b->id))->first()))
        ->assertOk()
        ->assertSee('Look at my shelf')
        ->assertSee('So pretty');

    $this->actingAs($me)->postJson(route('posts.send', $post), ['recipients' => []])->assertUnprocessable();
});

test('photos, voice messages and files can be sent, and only chat members can open them', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $outsider = User::factory()->create();
    $friend->following()->attach($me->id);

    $this->actingAs($me)->get(route('messages.start', $friend->username));
    $conversation = Conversation::firstOrFail();

    // Two photos and a caption in one message.
    $this->postJson(route('messages.store', $conversation), [
        'body' => 'My new shelf!',
        'photos' => [\Illuminate\Http\UploadedFile::fake()->image('a.jpg', 800, 600), \Illuminate\Http\UploadedFile::fake()->image('b.png', 600, 800)],
    ])->assertCreated()->assertSee('dm-photos--2', false);

    // A voice message.
    $this->postJson(route('messages.store', $conversation), [
        'voice' => \Illuminate\Http\UploadedFile::fake()->create('voice-message.webm', 40, 'audio/webm'),
        'voice_duration' => 7.4,
    ])->assertCreated()->assertSee('0:07');

    // A PDF.
    $this->postJson(route('messages.store', $conversation), [
        'files' => [\Illuminate\Http\UploadedFile::fake()->create('chapter-notes.pdf', 120, 'application/pdf')],
    ])->assertCreated()->assertSee('chapter-notes.pdf');

    $attachments = \App\Models\MessageAttachment::orderBy('id')->get();
    expect($attachments->pluck('type')->all())->toBe(['image', 'image', 'audio', 'file'])
        ->and($attachments[0]->width)->toBe(800)
        ->and($attachments[2]->duration)->toBe(7.4)
        ->and($attachments[0]->path)->toStartWith('message-attachments/' . $me->id . '/');
    \Illuminate\Support\Facades\Storage::disk('local')->assertExists($attachments[0]->path);

    // The inbox preview says what was sent.
    $this->actingAs($friend)->get(route('messages.index'))->assertSee('Sent a file');

    // Members can open attachments; nobody else can.
    $this->get(route('messages.attachment', $attachments[0]))->assertOk();
    $this->get(route('messages.attachment', $attachments[3]))->assertOk()->assertDownload('chapter-notes.pdf');
    $this->actingAs($outsider)->get(route('messages.attachment', $attachments[0]))->assertNotFound();

    // Unsafe or empty sends are refused.
    $this->actingAs($me)->postJson(route('messages.store', $conversation), [
        'files' => [\Illuminate\Http\UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')],
    ])->assertUnprocessable()->assertJsonValidationErrors('files.0');
    $this->postJson(route('messages.store', $conversation), [])->assertUnprocessable()->assertJsonValidationErrors('body');
});

test('the list refreshes, shows "Seen", and attachment files go when a request is deleted', function () {
    $me = User::factory()->create(['name' => 'Asker']);
    $other = User::factory()->create();

    $this->actingAs($me)->get(route('messages.start', $other->username));
    $conversation = Conversation::firstOrFail();
    $this->postJson(route('messages.store', $conversation), ['photos' => [\Illuminate\Http\UploadedFile::fake()->image('p.jpg')]]);
    $path = \App\Models\MessageAttachment::firstOrFail()->path;

    // Not seen yet…
    expect($this->getJson(route('messages.poll', $conversation))->json('seen_id'))->toBeNull();

    // …the other reader sees it in their (requests) list, opens it…
    $this->actingAs($other)->getJson(route('messages.list', ['tab' => 'requests']))
        ->assertOk()->assertJsonPath('requests', 1)->assertSee('Asker')->assertSee('Sent a photo');
    $this->getJson(route('messages.unread'))->assertJsonPath('unread', 1);
    $this->get(route('messages.show', $conversation));

    // …and now it's seen.
    $this->actingAs($me)->getJson(route('messages.poll', $conversation))
        ->assertJsonPath('seen_id', \App\Models\Message::firstOrFail()->id);

    // Deleting the request removes the stored photo too.
    $this->actingAs($other)->delete(route('messages.destroy', $conversation));
    \Illuminate\Support\Facades\Storage::disk('local')->assertMissing($path);
});
