<?php

use App\Models\Book;
use App\Models\BookComment;
use App\Models\BookCommentVote;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('book search shows the restored card design and rating controls', function () {
    Http::fake([
        'https://openlibrary.org/search.json*' => Http::response([
            'docs' => [[
                'key' => '/works/OL82563W',
                'title' => 'A Test Book',
                'author_name' => ['A. Author'],
                'cover_i' => 123,
            ]],
        ]),
    ]);

    $response = $this->actingAs(User::factory()->create())->get('/books?q=test');

    $response->assertOk()
        ->assertSee('Bookworm')
        ->assertSee('Find your next favorite book,')
        ->assertSee('A Test Book')
        ->assertSee('A. Author')
        ->assertSee('aria-label="Rate 0.5 stars"', false)
        ->assertSee('Book details & comments');
});

test('a user can save a half-star review and update it without creating duplicates', function () {
    $user = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL82563W',
        'title' => 'A Test Book',
    ]);

    $this->actingAs($user)->post(route('reviews.store', $book), [
        'rating' => 4.5,
        'body' => 'A first impression.',
    ])->assertRedirect(route('books.show', $book));

    $this->post(route('reviews.store', $book), [
        'rating' => 3.5,
        'body' => 'A revised impression.',
    ])->assertRedirect(route('books.show', $book));

    expect(Review::where('book_id', $book->id)->where('user_id', $user->id)->count())->toBe(1)
        ->and((float) Review::where('book_id', $book->id)->where('user_id', $user->id)->value('rating'))->toBe(3.5)
        ->and(Review::where('book_id', $book->id)->where('user_id', $user->id)->value('body'))->toBe('A revised impression.');

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertSee('Your thoughts (optional)')
        ->assertSee('Leave a comment')
        ->assertSee('Reader reviews');
});

test('a user can update their book comment and switch between like and dislike', function () {
    $author = User::factory()->create();
    $voter = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL82563W',
        'title' => 'A Test Book',
    ]);

    $this->actingAs($author)->post(route('books.comments.store', $book), [
        'body' => 'First comment.',
    ])->assertRedirect(route('books.show', $book))
        ->assertSessionHas('status', 'Your comment has been saved.');

    $comment = BookComment::firstOrFail();
    $this->actingAs($author)->post(route('books.comments.store', $book), [
        'body' => 'Updated discussion comment.',
    ])->assertRedirect(route('books.show', $book));

    $this->actingAs($voter)->post(route('books.comments.vote', [$book, $comment]), ['value' => 1]);
    $this->post(route('books.comments.vote', [$book, $comment]), ['value' => -1]);

    expect(BookComment::where('book_id', $book->id)->where('user_id', $author->id)->count())->toBe(1)
        ->and($comment->fresh()->body)->toBe('Updated discussion comment.')
        ->and(BookCommentVote::where('book_comment_id', $comment->id)->where('user_id', $voter->id)->value('value'))->toBe(-1);
});
