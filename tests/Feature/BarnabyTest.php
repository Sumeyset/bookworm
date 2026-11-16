<?php

use App\Models\Book;
use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use App\Models\BuddyRead;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'barnaby.providers' => ['gemini', 'groq'],
        'barnaby.gemini.key' => 'test-gemini-key',
        'barnaby.groq.key' => 'test-groq-key',
        'barnaby.per_minute' => 100,
        'barnaby.per_day' => 100,
    ]);
});

function geminiSays(string $text): array
{
    return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
}

test('Barnaby recommends books from a description, as cards linked to real books', function () {
    $me = User::factory()->create(['name' => 'Ada Reader']);
    $owned = Book::create(['ol_id' => 'OL-OWNED', 'title' => 'The Secret History']);
    $me->shelfItems()->create(['book_id' => $owned->id, 'status' => 'read']);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(geminiSays("Ooh, a dark academia mood! Here are some picks:\n```books\n[{\"title\":\"If We Were Villains\",\"author\":\"M. L. Rio\",\"why\":\"Shakespeare students and a death.\"},{\"title\":\"The Secret History\",\"author\":\"Donna Tartt\",\"why\":\"Already owned.\"}]\n```")),
        'openlibrary.org/*' => Http::response(['docs' => [['key' => '/works/OL123W', 'title' => 'If We Were Villains', 'author_name' => ['M. L. Rio'], 'cover_i' => 42, 'first_publish_year' => 2017]]]),
    ]);

    $response = $this->actingAs($me)->postJson(route('barnaby.chat'), ['message' => 'I want something like a moody campus mystery with obsessive friends'])
        ->assertOk()
        ->assertJsonPath('reply', 'Ooh, a dark academia mood! Here are some picks:')
        ->assertJsonCount(1, 'books') // the one already on the shelf is skipped
        ->assertJsonPath('books.0.title', 'If We Were Villains')
        ->assertJsonPath('books.0.why', 'Shakespeare students and a death.');

    $book = Book::where('ol_id', 'OL123W')->firstOrFail();
    expect($response->json('books.0.url'))->toBe(route('books.show', $book));

    // Barnaby saw the shelf, and the key went in a header (never in the URL).
    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'generativelanguage')
        && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
        && ! str_contains($request->url(), 'test-gemini-key')
        && str_contains(json_encode($request->data()), 'The Secret History'));
});

test('when Gemini is out of quota, Groq answers instead', function () {
    $me = User::factory()->create();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429),
        'api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Hello from the backup worm!']]]]),
    ]);

    $this->actingAs($me)->postJson(route('barnaby.chat'), ['message' => 'Hi Barnaby'])
        ->assertOk()
        ->assertJsonPath('reply', 'Hello from the backup worm!')
        ->assertJsonPath('books', []);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'api.groq.com') && $request->hasHeader('Authorization', 'Bearer test-groq-key'));
});

test('Barnaby is friendly about being offline, rate limited, or failing', function () {
    $me = User::factory()->create();

    config(['barnaby.gemini.key' => null, 'barnaby.groq.key' => null]);
    $this->actingAs($me)->postJson(route('barnaby.chat'), ['message' => 'Hi'])->assertStatus(503)->assertJsonPath('offline', true);

    config(['barnaby.gemini.key' => 'k', 'barnaby.groq.key' => 'k']);
    $up = false;
    Http::fake(function () use (&$up) {
        return $up ? Http::response(geminiSays('Hi!')) : Http::response('down', 500);
    });
    $this->postJson(route('barnaby.chat'), ['message' => 'Hi'])->assertStatus(503)->assertSee('lost between the pages');

    $up = true;
    config(['barnaby.per_day' => 2]);
    $other = User::factory()->create();
    $this->actingAs($other)->postJson(route('barnaby.chat'), ['message' => 'One'])->assertOk()->assertJsonPath('remaining', 1);
    $this->postJson(route('barnaby.chat'), ['message' => 'Two'])->assertOk();
    $this->postJson(route('barnaby.chat'), ['message' => 'Three'])->assertStatus(429)->assertJsonPath('limited', true);
});

test('explaining a passage and a spoiler-free recap send only what is needed', function () {
    $me = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-MM', 'title' => 'Middlemarch', 'author_name' => 'George Eliot']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading', 'current_page' => 120, 'total_pages' => 800]);
    $pdf = BookPdf::create(['user_id' => $me->id, 'book_id' => $book->id, 'path' => 'x.pdf', 'original_name' => 'x.pdf']);
    BookPdfHighlight::create(['book_pdf_id' => $pdf->id, 'user_id' => $me->id, 'page_number' => 40, 'text' => 'Early highlight', 'rectangles' => [['x' => 0, 'y' => 0, 'width' => .1, 'height' => .1]]]);
    BookPdfHighlight::create(['book_pdf_id' => $pdf->id, 'user_id' => $me->id, 'page_number' => 300, 'text' => 'Later highlight', 'rectangles' => [['x' => 0, 'y' => 0, 'width' => .1, 'height' => .1]]]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiSays('It means…'))]);

    $this->actingAs($me)->postJson(route('barnaby.explain'), ['text' => 'Who can quite enter into the feelings…', 'book_id' => $book->id, 'page' => 12])
        ->assertOk()->assertJsonPath('reply', 'It means…');

    $this->postJson(route('barnaby.recap', $book))->assertOk()->assertJsonPath('page', 120);

    Http::assertSent(function (Request $request) {
        $body = json_encode($request->data());

        return str_contains($body, 'up to page 120') && str_contains($body, 'Early highlight') && ! str_contains($body, 'Later highlight');
    });
});

test('buddy-read questions stay behind the slowest reader, for members only', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-BR', 'title' => 'Group Book']);
    $read = BuddyRead::create(['book_id' => $book->id, 'creator_id' => $me->id]);
    $read->members()->attach($me->id, ['joined_at' => now()]);
    $read->members()->attach($friend->id, ['joined_at' => now()]);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading', 'current_page' => 200]);
    $friend->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading', 'current_page' => 75]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiSays("1. What do you think of…"))]);

    $this->actingAs($me)->postJson(route('barnaby.discussion', $read))->assertOk()->assertJsonPath('page', 75);
    Http::assertSent(fn (Request $request) => str_contains(json_encode($request->data()), 'at least page 75'));

    $this->actingAs(User::factory()->create())->postJson(route('barnaby.discussion', $read))->assertNotFound();
});

test('who is this: Barnaby answers only up to the reader page', function () {
    $me = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-WHO', 'title' => 'Pride and Prejudice', 'author_name' => 'Jane Austen']);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(geminiSays('Mr. Darcy is a wealthy, proud gentleman Elizabeth meets at the ball.'))]);

    $this->actingAs($me)->postJson(route('barnaby.who'), ['name' => 'Mr. Darcy', 'book_id' => $book->id, 'page' => 42])
        ->assertOk()->assertJsonPath('reply', 'Mr. Darcy is a wealthy, proud gentleman Elizabeth meets at the ball.');

    Http::assertSent(fn (Request $request) => str_contains(json_encode($request->data()), 'only what a reader knows by page 42'));
});

test('the weekly pick is one book, the same all week, and notifies active readers', function () {
    $me = User::factory()->create();
    $read = Book::create(['ol_id' => 'OL-READ', 'title' => 'Rebecca']);
    $me->shelfItems()->create(['book_id' => $read->id, 'status' => 'read', 'current_page' => 10]);
    \App\Models\ReadingSession::create(['user_id' => $me->id, 'book_id' => $read->id, 'read_on' => today(), 'pages_read' => 10]);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(geminiSays("You loved Rebecca's gothic house.\n```books\n[{\"title\":\"The Thirteenth Tale\",\"author\":\"Diane Setterfield\",\"why\":\"Another house full of secrets.\"}]\n```")),
        'openlibrary.org/*' => Http::response(['docs' => [['key' => '/works/OL13W', 'title' => 'The Thirteenth Tale', 'author_name' => ['Diane Setterfield']]]]),
    ]);

    $this->actingAs($me)->getJson(route('barnaby.weekly'))->assertOk()->assertJsonPath('book.title', 'The Thirteenth Tale');
    $this->actingAs($me)->getJson(route('barnaby.weekly'))->assertOk();
    Http::assertSentCount(2); // one AI call + one Open Library lookup, then cached

    expect(\App\Support\Barnaby\WeeklyPicks::send())->toBe(1)
        ->and($me->notifications()->where('data->kind', 'barnaby')->first()->data['message'])->toContain('The Thirteenth Tale');
});
