<?php

use App\Models\Book;
use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use App\Models\Post;
use App\Models\User;

test('a quote is shown on the card design and typeface its author picked', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-CARD-1', 'title' => 'A Designed Book']);

    $this->actingAs($user)->post(route('posts.store'), [
        'type' => 'quote',
        'quote_text' => 'The sea was calm that night.',
        'book_id' => $book->id,
        'card_style' => 'midnight',
        'card_font' => 'hand',
    ])->assertRedirect(route('feed.index'));

    $post = Post::firstOrFail();
    expect($post->card_style)->toBe('midnight')->and($post->card_font)->toBe('hand');

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertSee('qcard--midnight', false)
        ->assertSee('qcard--f-hand', false)
        ->assertSee('The sea was calm that night.');

    $this->get(route('posts.show', $post))->assertOk()->assertSee('The sea was calm that night.');
});

test('a thought can go on a card, and unknown designs are refused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'body' => 'Up late reading.', 'card_style' => 'sunset', 'card_font' => 'sans'])
        ->assertRedirect(route('feed.index'));
    expect(Post::firstOrFail()->hasCard())->toBeTrue();

    $this->post(route('posts.store'), ['type' => 'post', 'body' => 'Hi', 'card_style' => 'neon'])->assertSessionHasErrors('card_style');
    $this->post(route('posts.store'), ['type' => 'post', 'body' => 'Hi', 'card_font' => 'comic'])->assertSessionHasErrors('card_font');

    $this->post(route('posts.store'), ['type' => 'post', 'body' => 'Just words.']);
    expect(Post::latest('id')->first()->hasCard())->toBeFalse();
});

test('the reader can share a highlight to the feed as a designed card', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-CARD-2', 'title' => 'A Shared Book']);
    $pdf = BookPdf::create(['user_id' => $user->id, 'book_id' => $book->id, 'path' => 'x.pdf', 'original_name' => 'x.pdf']);
    $highlight = BookPdfHighlight::create([
        'book_pdf_id' => $pdf->id,
        'user_id' => $user->id,
        'page_number' => 4,
        'text' => 'A line from the reader.',
        'rectangles' => [['x' => 0.1, 'y' => 0.1, 'width' => 0.2, 'height' => 0.02]],
    ]);

    $cardId = $this->actingAs($user)->postJson(route('quotes.store', [$pdf, $highlight]))
        ->assertCreated()
        ->json('quote_card_id');

    $response = $this->postJson(route('posts.store'), [
        'type' => 'quote',
        'quote_card_id' => $cardId,
        'body' => 'Had to share this.',
        'card_style' => 'paper',
        'card_font' => 'classic',
    ])->assertCreated();

    $post = Post::findOrFail($response->json('id'));
    expect($post->quote_text)->toBe('A line from the reader.')
        ->and($post->quote_page)->toBe(4)
        ->and($post->book_id)->toBe($book->id)
        ->and($post->card_style)->toBe('paper');
    expect($response->json('url'))->toBe(route('posts.show', $post));
    expect($response->json('share_url'))->not->toBeNull();

    // Someone else can't post your saved quote.
    $this->actingAs(User::factory()->create())
        ->postJson(route('posts.store'), ['type' => 'quote', 'quote_card_id' => $cardId])
        ->assertUnprocessable();
});
