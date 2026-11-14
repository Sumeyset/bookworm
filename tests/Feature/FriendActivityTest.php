<?php

use App\Models\Book;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;

test('ratings and reviews stay out of the feed', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $me->following()->attach($friend->id);
    $book = Book::create(['ol_id' => 'OL-FA-1', 'title' => 'A Rated Book']);

    Review::create(['user_id' => $friend->id, 'book_id' => $book->id, 'rating' => 4, 'body' => 'A quiet, lovely review.']);
    Post::create(['user_id' => $friend->id, 'type' => 'post', 'body' => 'A friend’s thought.']);

    $this->actingAs($me)->get(route('feed.index'))
        ->assertOk()
        ->assertSee('A friend’s thought.')
        ->assertSee('id="post-', false)
        ->assertDontSee('id="review-', false);

    // …but the follower hears about the review in their notifications.
    expect($me->notifications()->first()->data['message'])->toContain('A quiet, lovely review.');
});

test('followers are told when a friend starts, finishes, rates or reviews a book', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create(['name' => 'Ada Reader']);
    $me->following()->attach($friend->id);
    $book = Book::create(['ol_id' => 'OL-FA-2', 'title' => 'The Long Winter']);

    $shelf = $friend->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading']);
    expect($me->notifications()->first()->data)
        ->kind->toBe('reading')
        ->message->toBe('Ada Reader started reading “The Long Winter”.');

    $shelf->update(['status' => 'read']);
    expect($me->notifications()->where('data->kind', 'finished')->first()->data['message'])->toBe('Ada Reader finished “The Long Winter”.');

    $review = Review::create(['user_id' => $friend->id, 'book_id' => $book->id, 'rating' => 4.5]);
    expect($me->notifications()->where('data->kind', 'rated')->first()->data['message'])->toBe('Ada Reader rated “The Long Winter” 4.5★.');

    $review->update(['body' => 'Cold, bright and kind.']);
    expect($me->notifications()->where('data->kind', 'reviewed')->first()->data['message'])->toContain('Cold, bright and kind.');

    // Moving the book back and forth doesn't repeat the news.
    $shelf->update(['status' => 'reading']);
    $shelf->update(['status' => 'read']);
    expect($me->notifications()->count())->toBe(4);

    // The reader themselves isn't notified.
    expect($friend->notifications()->count())->toBe(0);
});

test('a review when finishing a book sends one notification, not two', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $me->following()->attach($friend->id);
    $book = Book::create(['ol_id' => 'OL-FA-3', 'title' => 'Short Book']);

    $this->actingAs($friend)->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Loved it.']);

    expect($me->notifications()->pluck('data')->pluck('kind')->all())->toBe(['reviewed']);
});
