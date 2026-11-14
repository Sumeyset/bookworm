<?php

use App\Models\Book;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;

test('a reader can write a post and share a typed quote on the feed', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-POST-1', 'title' => 'A Postable Book']);
    $user->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading']);

    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'body' => 'Loving the slow start.', 'book_id' => $book->id])
        ->assertRedirect(route('feed.index'));
    $this->post(route('posts.store'), ['type' => 'quote', 'quote_text' => 'All happy families are alike.', 'quote_page' => 1, 'book_id' => $book->id])
        ->assertRedirect(route('feed.index'));

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertSee('Loving the slow start.')
        ->assertSee('All happy families are alike.')
        ->assertSee('A Postable Book');

    $this->get(route('quotes.index'))->assertOk()->assertSee('All happy families are alike.');
});

test('posts need text, and a quote needs the quote', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'body' => ''])->assertSessionHasErrors('body');
    $this->post(route('posts.store'), ['type' => 'quote', 'quote_text' => ''])->assertSessionHasErrors('quote_text');
    expect(Post::count())->toBe(0);
});

test('only the author can delete a post', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $post = Post::create(['user_id' => $author->id, 'type' => 'post', 'body' => 'Mine.']);

    $this->actingAs($other)->delete(route('posts.destroy', $post))->assertForbidden();
    $this->actingAs($author)->delete(route('posts.destroy', $post))->assertRedirect();
    expect(Post::count())->toBe(0);
});

test('readers can like and reply to posts and reviews, and the author is notified', function () {
    $author = User::factory()->create();
    $fan = User::factory()->create();
    $post = Post::create(['user_id' => $author->id, 'type' => 'post', 'body' => 'Thoughts.']);
    $book = Book::create(['ol_id' => 'OL-POST-2', 'title' => 'Reviewed Book']);
    $review = Review::create(['user_id' => $author->id, 'book_id' => $book->id, 'rating' => 4]);

    $this->actingAs($fan)->postJson(route('reactions.like', ['post', $post->id]))->assertOk()->assertJson(['liked' => true, 'count' => 1]);
    $this->postJson(route('reactions.like', ['post', $post->id]))->assertOk()->assertJson(['liked' => false, 'count' => 0]);
    $this->postJson(route('reactions.like', ['review', $review->id]))->assertOk()->assertJson(['liked' => true]);
    $this->post(route('reactions.comment', ['post', $post->id]), ['body' => 'So true!'])->assertRedirect();

    expect($post->comments()->count())->toBe(1)
        ->and($author->notifications()->count())->toBe(3);

    $this->actingAs($author)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee($fan->name . ' liked your post.')
        ->assertSee('So true!');
});

test('the following feed only shows the reader and people they follow; everyone shows all', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $stranger = User::factory()->create();
    $me->following()->attach($friend->id);
    Post::create(['user_id' => $friend->id, 'type' => 'post', 'body' => 'From a friend.']);
    Post::create(['user_id' => $stranger->id, 'type' => 'post', 'body' => 'From a stranger.']);

    $this->actingAs($me)->get(route('feed.index'))->assertSee('From a friend.')->assertDontSee('From a stranger.');
    $this->get(route('feed.index', ['scope' => 'everyone']))->assertSee('From a stranger.');
});

test('following someone and reviewing a book they want to read sends notifications', function () {
    $reader = User::factory()->create();
    $critic = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-POST-3', 'title' => 'Wanted Book']);
    $reader->shelfItems()->create(['book_id' => $book->id, 'status' => 'want_to_read']);

    $this->actingAs($reader)->post(route('users.follow', $critic->username));
    expect($critic->notifications()->first()->data['kind'])->toBe('follow');

    $this->actingAs($critic)->post(route('reviews.store', $book), ['rating' => 5, 'body' => 'Wonderful.']);
    expect($reader->notifications()->first()->data['message'])->toContain('Wanted Book');
});
