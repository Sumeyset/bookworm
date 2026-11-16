<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Support\BookInsights;

test('reviews carry moods and pace, and the book page sums them up', function () {
    $me = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-MOOD', 'title' => 'Moody Book']);

    $this->actingAs($me)->post(route('reviews.store', $book), ['rating' => 4, 'moods' => ['cosy', 'mysterious'], 'pace' => 'slow'])->assertRedirect();
    expect(Review::first()->moods)->toBe(['cosy', 'mysterious'])->and(Review::first()->pace)->toBe('slow');

    $this->actingAs($me)->post(route('reviews.store', $book), ['rating' => 4, 'moods' => ['cosy', 'dark', 'sad', 'funny']])->assertSessionHasErrors('moods');

    $this->actingAs($me)->get(route('books.show', $book))->assertOk()->assertSee('Readers say it’s…')->assertSee('Cosy')->assertSee('How did it feel?');
});

test('readers who loved this also loved', function () {
    $book = Book::create(['ol_id' => 'OL-A', 'title' => 'Book A']);
    $b = Book::create(['ol_id' => 'OL-B', 'title' => 'Book B']);
    $c = Book::create(['ol_id' => 'OL-C', 'title' => 'Book C']);
    foreach (range(1, 3) as $i) {
        $u = User::factory()->create();
        Review::withoutEvents(fn () => Review::create(['user_id' => $u->id, 'book_id' => $book->id, 'rating' => 5]));
        Review::withoutEvents(fn () => Review::create(['user_id' => $u->id, 'book_id' => $b->id, 'rating' => 4.5]));
        if ($i === 1) {
            Review::withoutEvents(fn () => Review::create(['user_id' => $u->id, 'book_id' => $c->id, 'rating' => 4]));
        }
    }

    $also = BookInsights::alsoLoved($book);
    expect($also->pluck('title')->all())->toBe(['Book B', 'Book C'])->and($also->first()->fans)->toBe(3);
    $this->actingAs(User::factory()->create())->get(route('books.show', $book))->assertSee('Readers who loved this also loved')->assertSee('3 readers loved both');
});
