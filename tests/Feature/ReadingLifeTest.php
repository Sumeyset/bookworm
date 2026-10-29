<?php

use App\Models\Book;
use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use App\Models\QuoteCard;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Facades\Storage;

test('a reader can track shelf status and page progress without losing progress on status changes', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-SHELF-1', 'title' => 'A Shelf Book']);

    $this->actingAs($user)->post(route('books.shelf.update', $book), [
        'status' => 'reading',
        'current_page' => 42,
        'total_pages' => 240,
    ])->assertSessionHasNoErrors();

    $shelfItem = UserBookShelf::where('user_id', $user->id)->where('book_id', $book->id)->firstOrFail();
    expect($shelfItem->status)->toBe('reading')
        ->and($shelfItem->current_page)->toBe(42)
        ->and($shelfItem->total_pages)->toBe(240);

    $this->post(route('books.shelf.update', $book), ['status' => 'read'])
        ->assertSessionHasNoErrors();

    $shelfItem->refresh();
    expect($shelfItem->status)->toBe('read')
        ->and($shelfItem->current_page)->toBe(42)
        ->and($shelfItem->total_pages)->toBe(240)
        ->and($shelfItem->read_at)->not->toBeNull();
    $completedAt = $shelfItem->read_at->toDateTimeString();

    $this->travel(2)->days();
    $this->post(route('books.shelf.update', $book), ['status' => 'read']);

    expect($shelfItem->fresh()->read_at->toDateTimeString())->toBe($completedAt);

    $this->get(route('library.index', ['status' => 'read', 'sort' => 'title']))
        ->assertRedirect(route('users.show', ['user' => $user->username, 'status' => 'read', 'sort' => 'title']));

    $this->get(route('users.show', ['user' => $user->username, 'status' => 'read', 'sort' => 'title']))
        ->assertOk()
        ->assertSee('A Shelf Book');
});

test('reading progress rejects a current page beyond the PDF page count', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-SHELF-2', 'title' => 'A Validated Book']);

    $this->actingAs($user)->postJson(route('books.shelf.update', $book), [
        'status' => 'reading',
        'current_page' => 101,
        'total_pages' => 100,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('total_pages');

    expect(UserBookShelf::count())->toBe(0);
});

test('writing a review marks a book read while preserving its saved page progress', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-SHELF-4', 'title' => 'A Reviewed Book']);
    $shelfItem = UserBookShelf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => 'reading',
        'current_page' => 80,
        'total_pages' => 300,
    ]);

    $this->actingAs($user)->post(route('reviews.store', $book), [
        'rating' => 4.5,
        'body' => 'A thoughtful review.',
    ])->assertSessionHasNoErrors();

    expect($shelfItem->fresh()->status)->toBe('read')
        ->and($shelfItem->fresh()->current_page)->toBe(80)
        ->and($shelfItem->fresh()->total_pages)->toBe(300)
        ->and($shelfItem->fresh()->read_at)->not->toBeNull();
});

test('a rating marks an existing shelf entry read without clearing progress', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-SHELF-5', 'title' => 'A Rated Book']);
    $shelfItem = UserBookShelf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => 'reading',
        'current_page' => 32,
        'total_pages' => 210,
    ]);

    $this->actingAs($user)->post(route('books.rate'), [
        'ol_id' => $book->ol_id,
        'title' => $book->title,
        'rating' => 4.5,
    ])->assertSessionHasNoErrors();

    expect($shelfItem->fresh()->status)->toBe('read')
        ->and($shelfItem->fresh()->current_page)->toBe(32)
        ->and($shelfItem->fresh()->total_pages)->toBe(210)
        ->and($shelfItem->fresh()->read_at)->not->toBeNull();
});

test('the private PDF reader restores and saves page progress', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-SHELF-3', 'title' => 'A Reading Book']);
    $pdf = BookPdf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'path' => 'book-pdfs/' . $user->id . '/' . $book->id . '/reading.pdf',
        'original_name' => 'reading.pdf',
    ]);
    Storage::disk('local')->put($pdf->path, "%PDF-1.4\nPrivate reading copy\n%%EOF");
    $shelfItem = UserBookShelf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'status' => 'reading',
        'current_page' => 42,
        'total_pages' => 240,
    ]);

    $this->actingAs($user)->get(route('books.pdfs.reader', [$book, $pdf]))
        ->assertOk()
        ->assertSee('data-initial-page="42"', false);

    $this->postJson(route('books.shelf.update', $book), [
        'status' => 'reading',
        'current_page' => 55,
        'total_pages' => 240,
    ])->assertOk()
        ->assertJsonPath('saved', true);

    expect($shelfItem->fresh()->current_page)->toBe(55)
        ->and($shelfItem->fresh()->total_pages)->toBe(240);
});

test('onboarding saves genre preferences and yearly reading goal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.store'), [
        'preferred_genres' => ['Fantasy', 'Mystery'],
        'yearly_reading_goal' => 24,
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('books.index'));

    expect($user->fresh()->preferred_genres)->toBe(['Fantasy', 'Mystery'])
        ->and($user->fresh()->yearly_reading_goal)->toBe(24)
        ->and($user->fresh()->onboarding_completed_at)->not->toBeNull();

    $this->get(route('books.index'))
        ->assertOk()
        ->assertSee('Explore a favorite kind of story')
        ->assertSee(route('books.index', ['q' => 'Fantasy']), false);
});

test('onboarding rejects invalid genre values and reading goals', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from(route('onboarding.show'))->post(route('onboarding.store'), [
        'preferred_genres' => ['Not a real genre'],
        'yearly_reading_goal' => 0,
    ])->assertRedirect(route('onboarding.show'))
        ->assertSessionHasErrors(['preferred_genres.0', 'yearly_reading_goal']);
});

test('a reader can skip onboarding and revisit preferences later', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.skip'))
        ->assertRedirect(route('books.index'));

    expect($user->fresh()->onboarding_completed_at)->not->toBeNull();

    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('Update your reading nook')
        ->assertSee('Save my preferences');
});

test('the activity feed only shows reviews written by followed readers', function () {
    $reader = User::factory()->create();
    $followed = User::factory()->create();
    $notFollowed = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-FEED-1', 'title' => 'Feed Book']);
    $reader->following()->attach($followed);

    Review::create([
        'user_id' => $followed->id,
        'book_id' => $book->id,
        'rating' => 4.5,
        'body' => 'A review from someone I follow.',
    ]);
    Review::create([
        'user_id' => $notFollowed->id,
        'book_id' => $book->id,
        'rating' => 3.0,
        'body' => 'A review outside my feed.',
    ]);

    $this->actingAs($reader)->get(route('feed.index'))
        ->assertOk()
        ->assertSee('A review from someone I follow.')
        ->assertDontSee('A review outside my feed.');
});

test('a reader profile does not expose another users want-to-read or in-progress shelf items', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $privateBook = Book::create(['ol_id' => 'OL-SHELF-PRIVATE', 'title' => 'Private Reading Choice']);
    $publicBook = Book::create(['ol_id' => 'OL-SHELF-PUBLIC', 'title' => 'A Shared Review']);

    UserBookShelf::create([
        'user_id' => $owner->id,
        'book_id' => $privateBook->id,
        'status' => 'reading',
        'current_page' => 80,
        'total_pages' => 300,
    ]);
    UserBookShelf::create([
        'user_id' => $owner->id,
        'book_id' => $publicBook->id,
        'status' => 'read',
        'read_at' => now(),
    ]);
    Review::create([
        'user_id' => $owner->id,
        'book_id' => $publicBook->id,
        'rating' => 4,
        'body' => 'A public review.',
    ]);

    $this->actingAs($visitor)->get(route('users.show', $owner->username))
        ->assertOk()
        ->assertSee('A Shared Review')
        ->assertDontSee('Private Reading Choice');
});

test('saved quotes are private by default and sharing can be revoked', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-QUOTE-1', 'title' => 'A Quoted Book']);
    $pdf = BookPdf::create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'path' => 'book-pdfs/' . $owner->id . '/' . $book->id . '/quote.pdf',
        'original_name' => 'quote.pdf',
    ]);
    $highlight = BookPdfHighlight::create([
        'book_pdf_id' => $pdf->id,
        'user_id' => $owner->id,
        'page_number' => 12,
        'text' => 'A sentence worth remembering.',
        'rectangles' => [['x' => 0.1, 'y' => 0.2, 'width' => 0.3, 'height' => 0.04]],
    ]);
    $this->actingAs($otherUser)
        ->postJson(route('quotes.store', [$pdf, $highlight]))
        ->assertNotFound();

    $this->actingAs($owner)->postJson(route('quotes.store', [$pdf, $highlight]))
        ->assertCreated()
        ->assertJsonPath('saved', true);
    $quote = QuoteCard::where('book_pdf_highlight_id', $highlight->id)->firstOrFail();

    $this->get(route('quotes.public', $quote->share_token))->assertNotFound();
    $this->actingAs($otherUser)->get('/quotes')->assertNotFound();
    $this->actingAs($otherUser)->get(route('books.show', $book))
        ->assertOk()
        ->assertDontSee('A sentence worth remembering.');

    $this->actingAs($owner)->get(route('books.show', $book))
        ->assertOk()
        ->assertSee('Quotes from this book')
        ->assertSee('A sentence worth remembering.')
        ->assertSee('Share quote');

    $this->actingAs($otherUser)->post(route('quotes.share', $quote))->assertNotFound();
    $this->actingAs($owner)->post(route('quotes.share', $quote))->assertRedirect();
    $this->get(route('quotes.public', $quote->share_token))
        ->assertOk()
        ->assertSee('A sentence worth remembering.')
        ->assertSee('A Quoted Book');
    $this->actingAs($otherUser)->get(route('books.show', $book))
        ->assertOk()
        ->assertSee('A sentence worth remembering.');

    $this->actingAs($owner)->post(route('quotes.share', $quote))->assertRedirect();
    $this->get(route('quotes.public', $quote->share_token))->assertNotFound();
});
