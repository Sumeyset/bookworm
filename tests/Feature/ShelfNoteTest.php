<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

test('friends leave sticky notes on a book; only the owner and the author see them', function () {
    $owner = User::factory()->create();
    $friend = User::factory()->create(['name' => 'Noted Nell']);
    $other = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-NOTE', 'title' => 'Note Book']);
    $item = $owner->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'read_at' => now()]);
    Review::withoutEvents(fn () => Review::create(['user_id' => $owner->id, 'book_id' => $book->id, 'rating' => 5]));

    $this->actingAs($friend)->postJson(route('shelf-notes.store', $item), ['body' => 'You will love the ending!', 'color' => 'pink'])
        ->assertOk()->assertJsonPath('notes.0.body', 'You will love the ending!')->assertJsonPath('notes.0.author', 'You');
    expect($owner->notifications()->where('data->kind', 'note')->count())->toBe(1);

    // The owner sees it (with a tab on the book); another visitor doesn't.
    $this->actingAs($owner)->get(route('users.show', $owner->username))->assertSee('tree-book__sticky', false)->assertSee('Noted Nell');
    $this->actingAs($other)->get(route('users.show', $owner->username))->assertDontSee('You will love the ending!');

    // You can't leave notes on your own shelf, and private shelves need following.
    $this->actingAs($owner)->postJson(route('shelf-notes.store', $item), ['body' => 'mine'])->assertNotFound();
    $owner->forceFill(['is_private' => true])->save();
    $this->actingAs($other)->postJson(route('shelf-notes.store', $item), ['body' => 'hi'])->assertNotFound();

    // The owner can remove a note.
    $note = \App\Models\ShelfNote::first();
    $this->actingAs($owner)->deleteJson(route('shelf-notes.destroy', $note))->assertOk()->assertJsonCount(0, 'notes');
});
