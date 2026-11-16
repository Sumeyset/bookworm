<?php

use App\Models\Book;
use App\Models\User;

test('author pages list the books and friends who read them', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create(['name' => 'Friendly Fern']);
    $me->following()->attach($friend->id);
    $a = Book::create(['ol_id' => 'OL-AU1', 'title' => 'Emma', 'author_name' => 'Jane Austen', 'first_publish_year' => 1815]);
    Book::create(['ol_id' => 'OL-AU2', 'title' => 'Persuasion', 'author_name' => 'Jane Austen', 'first_publish_year' => 1817]);
    $friend->shelfItems()->create(['book_id' => $a->id, 'status' => 'read', 'read_at' => now()]);

    $this->actingAs($me)->get(route('books.show', $a))->assertSee(route('authors.show', rawurlencode('Jane Austen')), false);
    $this->actingAs($me)->get(route('authors.show', rawurlencode('jane austen')))
        ->assertOk()->assertSee('Jane Austen')->assertSee('Persuasion')->assertSee('Friendly Fern');
    $this->actingAs($me)->get(route('authors.show', 'Nobody Atall'))->assertNotFound();
});
