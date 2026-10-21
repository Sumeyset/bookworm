<?php

use App\Models\Book;
use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload and download multiple private PDFs for a book', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-1',
        'title' => 'A Test Book',
    ]);

    $this->actingAs($user)
        ->post(route('books.pdfs.store', $book), [
            'pdfs' => [
                UploadedFile::fake()->createWithContent('notes.pdf', "%PDF-1.4\nPrivate notes\n%%EOF"),
                UploadedFile::fake()->createWithContent('reading-list.pdf', "%PDF-1.4\nReading list\n%%EOF"),
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('books.show', $book));

    $pdfs = BookPdf::where('user_id', $user->id)->where('book_id', $book->id)->get();

    expect($pdfs)->toHaveCount(2);

    foreach ($pdfs as $pdf) {
        Storage::disk('local')->assertExists($pdf->path);
        expect($pdf->path)->toStartWith('book-pdfs/' . $user->id . '/' . $book->id . '/');
    }

    $this->get(route('books.pdfs.download', [$book, $pdfs->first()]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload($pdfs->first()->original_name);

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertSee('Your PDFs for this book')
        ->assertSee('notes.pdf')
        ->assertSee('reading-list.pdf');

    $firstPdf = $pdfs->first();
    $this->delete(route('books.pdfs.destroy', [$book, $firstPdf]))
        ->assertRedirect(route('books.show', $book));

    Storage::disk('local')->assertMissing($firstPdf->path);
    expect($firstPdf->fresh())->toBeNull();
});

test('a user cannot access or delete another users book PDFs', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-2',
        'title' => 'A Private Book',
    ]);
    $path = 'book-pdfs/' . $owner->id . '/' . $book->id . '/private-notes.pdf';
    Storage::disk('local')->put($path, "%PDF-1.4\nPrivate notes\n%%EOF");
    $pdf = BookPdf::create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'path' => $path,
        'original_name' => 'private-notes.pdf',
    ]);

    $this->actingAs($otherUser)
        ->get(route('books.pdfs.download', [$book, $pdf]))
        ->assertNotFound();

    $this->get(route('books.show', $book))
        ->assertOk()
        ->assertDontSee('private-notes.pdf');

    $this->delete(route('books.pdfs.destroy', [$book, $pdf]))
        ->assertNotFound();

    Storage::disk('local')->assertExists($path);
    expect($pdf->fresh())->not->toBeNull();
});

test('book PDF uploads require valid PDF files', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-3',
        'title' => 'Another Test Book',
    ]);

    $this->actingAs($user)
        ->from(route('books.show', $book))
        ->post(route('books.pdfs.store', $book), [
            'pdfs' => [UploadedFile::fake()->create('not-a-pdf.txt', 10, 'text/plain')],
        ])
        ->assertSessionHasErrors('pdfs.0');

    expect(BookPdf::count())->toBe(0);
});

test('deleting an account removes its private book PDFs', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-4',
        'title' => 'A Book to Remove',
    ]);
    $path = 'book-pdfs/' . $user->id . '/' . $book->id . '/notes.pdf';
    Storage::disk('local')->put($path, "%PDF-1.4\nPrivate notes\n%%EOF");
    BookPdf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'path' => $path,
        'original_name' => 'notes.pdf',
    ]);

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertRedirect('/');

    Storage::disk('local')->assertMissing($path);
    expect(BookPdf::count())->toBe(0);
});

test('a user can read a private PDF and save and remove text highlights', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-5',
        'title' => 'A Book to Read',
    ]);
    $path = 'book-pdfs/' . $user->id . '/' . $book->id . '/reading.pdf';
    Storage::disk('local')->put($path, "%PDF-1.4\nPrivate reading copy\n%%EOF");
    $pdf = BookPdf::create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'path' => $path,
        'original_name' => 'reading.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('books.pdfs.reader', [$book, $pdf]))
        ->assertOk()
        ->assertSee('Highlight selection')
        ->assertSee('Saved highlights');

    $fileResponse = $this->get(route('books.pdfs.file', [$book, $pdf]));
    $fileResponse->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="book.pdf"');

    $highlightResponse = $this->postJson(route('books.pdfs.highlights.store', [$book, $pdf]), [
        'page_number' => 1,
        'text' => 'A selected sentence',
        'rectangles' => [
            ['x' => 0.1, 'y' => 0.2, 'width' => 0.5, 'height' => 0.03],
        ],
    ])->assertCreated()
        ->assertJsonPath('text', 'A selected sentence');

    $highlightId = $highlightResponse->json('id');

    $this->getJson(route('books.pdfs.highlights.index', [$book, $pdf]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.text', 'A selected sentence');

    $this->deleteJson(route('books.pdfs.highlights.destroy', [$book, $pdf, $highlightId]))
        ->assertOk()
        ->assertJsonPath('deleted', true);

    expect(BookPdfHighlight::count())->toBe(0);
});

test('book PDF highlights and reader are private to the PDF owner', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $book = Book::create([
        'ol_id' => 'OL-PDF-6',
        'title' => 'Another Private Book',
    ]);
    $path = 'book-pdfs/' . $owner->id . '/' . $book->id . '/private-reading.pdf';
    Storage::disk('local')->put($path, "%PDF-1.4\nPrivate copy\n%%EOF");
    $pdf = BookPdf::create([
        'user_id' => $owner->id,
        'book_id' => $book->id,
        'path' => $path,
        'original_name' => 'private-reading.pdf',
    ]);

    $this->actingAs($owner)->postJson(route('books.pdfs.highlights.store', [$book, $pdf]), [
        'page_number' => 1,
        'text' => 'Owner-only highlight',
        'rectangles' => [
            ['x' => 0.1, 'y' => 0.2, 'width' => 0.5, 'height' => 0.03],
        ],
    ])->assertCreated();

    $this->actingAs($otherUser)
        ->get(route('books.pdfs.reader', [$book, $pdf]))
        ->assertNotFound();

    $this->get(route('books.pdfs.file', [$book, $pdf]))
        ->assertNotFound();

    $this->getJson(route('books.pdfs.highlights.index', [$book, $pdf]))
        ->assertNotFound();
});
