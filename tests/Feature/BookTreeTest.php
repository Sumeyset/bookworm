<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;

function shelveBooks(User $user, int $count, string $status = 'want_to_read'): array
{
    return collect(range(1, $count))->map(function ($i) use ($user, $status) {
        $book = Book::create(['ol_id' => 'OL-TREE-' . $user->id . '-' . $status . '-' . $i, 'title' => 'Tree Book ' . $status . ' ' . $i]);
        UserBookShelf::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => $status]);

        return $book;
    })->all();
}

test('new books are placed on the first branch with room so they always show on the tree', function () {
    $user = User::factory()->create();
    shelveBooks($user, 7);

    $slots = UserBookShelf::where('user_id', $user->id)->orderBy('id')->pluck('shelf_slot')->all();
    expect($slots)->toBe([0, 0, 0, 1, 1, 1, 2]);

    $this->actingAs($user)->get(route('users.show', $user->username))
        ->assertOk()
        ->assertSee('Tree Book want_to_read 1')
        ->assertSee('Tree Book want_to_read 7');
});

test('adding a book from the book page puts it on the tree', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-TREE-ADD', 'title' => 'Freshly Added']);

    $this->actingAs($user)->post(route('books.shelf.update', $book), ['status' => 'want_to_read']);

    $this->get(route('users.show', $user->username))
        ->assertSee('aria-label="Freshly Added, Want to read"', false);
});

test('the tree picture shows 7 shelves and grows two shelves at a time', function () {
    $user = User::factory()->create();
    shelveBooks($user, 10);

    $response = $this->actingAs($user)->get(route('users.show', $user->username));
    expect($response->viewData('treeShelves'))->toHaveCount(7)
        ->and($response->getContent())->toContain('images/tree/tree-shelf.webp');

    // 21 books fill all 7 planks, so the owner gets two new planks to grow into.
    shelveBooks($user, 11, 'read');
    $response = $this->get(route('users.show', $user->username));
    expect($response->viewData('treeShelves'))->toHaveCount(9)
        ->and($response->viewData('treeShelves')->last()['sprouting'])->toBeTrue();
});

test('every plank in the grown picture gets a position', function () {
    foreach ([7, 9, 15] as $count) {
        $layout = \App\Support\TreeBackdrop::layout($count);
        expect($layout['shelves'])->toHaveCount($count)
            ->and($layout['height'])->toBe(937 + ($count - 7) / 2 * 142);
        // Lower slots sit lower on the picture.
        $bottoms = array_column($layout['shelves'], 'bottom');
        expect($bottoms)->toBe(collect($bottoms)->sort()->values()->all());
    }
});

test('a book can be dragged to a branch with room', function () {
    $user = User::factory()->create();
    [$first] = shelveBooks($user, 4);

    $this->actingAs($user)->patchJson(route('shelf.entry.move', $first), ['slot' => 1, 'position' => 0])
        ->assertOk()
        ->assertJsonPath('shelves.1.0', $first->id);

    $item = UserBookShelf::where('book_id', $first->id)->first();
    expect($item->shelf_slot)->toBe(1)->and($item->shelf_order)->toBe(0)
        ->and($item->status)->toBe('want_to_read');
    expect(UserBookShelf::where('user_id', $user->id)->where('shelf_slot', 0)->orderBy('shelf_order')->pluck('shelf_order')->all())
        ->toBe([0, 1]);
});

test('dropping onto a full branch swaps the two books', function () {
    $user = User::factory()->create();
    $books = shelveBooks($user, 6);

    $this->actingAs($user)->patchJson(route('shelf.entry.move', $books[0]), ['slot' => 1, 'position' => 1])
        ->assertOk();

    expect(UserBookShelf::where('book_id', $books[0]->id)->value('shelf_slot'))->toBe(1)
        ->and(UserBookShelf::where('book_id', $books[4]->id)->value('shelf_slot'))->toBe(0)
        ->and(UserBookShelf::where('user_id', $user->id)->where('shelf_slot', 1)->count())->toBe(3);
});

test('the book popup saves status, bookmark, note and rating', function () {
    $user = User::factory()->create();
    [$book] = shelveBooks($user, 1);

    $this->actingAs($user)->patchJson(route('shelf.entry.update', $book), [
        'status' => 'reading',
        'current_page' => 120,
        'total_pages' => 300,
        'note' => 'Loved chapter four.',
        'rating' => 4.5,
    ])->assertOk()
        ->assertJsonPath('book.progress', 40)
        ->assertJsonPath('book.note', 'Loved chapter four.')
        ->assertJsonPath('book.rating', 4.5);

    $item = UserBookShelf::where('book_id', $book->id)->first();
    expect($item->status)->toBe('reading')
        ->and($item->started_at)->not->toBeNull()
        ->and((float) Review::where('book_id', $book->id)->value('rating'))->toBe(4.5);

    $this->patchJson(route('shelf.entry.update', $book), ['current_page' => 400])
        ->assertUnprocessable();
});

test('readers cannot move or edit someone else’s books', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    [$book] = shelveBooks($owner, 1);

    $this->actingAs($other)->patchJson(route('shelf.entry.update', $book), ['note' => 'hi'])->assertNotFound();
    $this->actingAs($other)->patchJson(route('shelf.entry.move', $book), ['slot' => 2])->assertNotFound();
});

test('visitors never see private notes or bookmarks in the tree data', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-TREE-PRIVATE', 'title' => 'Shared Favourite']);
    UserBookShelf::create([
        'user_id' => $owner->id, 'book_id' => $book->id, 'status' => 'read',
        'read_at' => now(), 'note' => 'SECRET-NOTE', 'current_page' => 10, 'total_pages' => 10,
    ]);
    Review::create(['user_id' => $owner->id, 'book_id' => $book->id, 'rating' => 5, 'body' => 'Public words.']);

    $this->actingAs($visitor)->get(route('users.show', $owner->username))
        ->assertOk()
        ->assertSee('Shared Favourite')
        ->assertDontSee('SECRET-NOTE')
        ->assertDontSee('save_url');
});
