<?php

use App\Models\Book;
use App\Models\BuddyRead;
use App\Models\ReadingSession;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;
use App\Support\CoverCache;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('a Goodreads export is imported onto the tree with statuses, ratings and reviews', function () {
    $user = User::factory()->create();
    $csv = implode("\n", [
        'Book Id,Title,Author,ISBN,ISBN13,My Rating,Number of Pages,Original Publication Year,Date Read,Date Added,Exclusive Shelf,My Review',
        '1,The Hobbit,J.R.R. Tolkien,="0261102214",="9780261102217",5,310,1937,2026/03/02,2026/01/01,read,"Second breakfast, please."',
        '2,Dune,Frank Herbert,,,0,,1965,,2026/02/01,currently-reading,',
        '3,Emma,Jane Austen,,,0,,1815,,2026/02/01,to-read,',
    ]);
    $file = UploadedFile::fake()->createWithContent('goodreads_library_export.csv', $csv);

    $this->actingAs($user)->post(route('import.store'), ['file' => $file])
        ->assertRedirect(route('users.show', $user->username))
        ->assertSessionHas('status', fn ($status) => str_contains($status, 'Imported 3 books from Goodreads'));

    $items = $user->shelfItems()->with('book')->get()->keyBy('book.title');
    expect($items)->toHaveCount(3)
        ->and($items['The Hobbit']->status)->toBe('read')
        ->and($items['The Hobbit']->book->isbn)->toBe('9780261102217')
        ->and($items['Dune']->status)->toBe('reading')
        ->and($items['Emma']->status)->toBe('want_to_read')
        ->and(Review::where('user_id', $user->id)->value('body'))->toBe('Second breakfast, please.');

    // Importing again skips books that are already on the tree.
    $again = UploadedFile::fake()->createWithContent('again.csv', $csv);
    $this->post(route('import.store'), ['file' => $again]);
    expect($user->shelfItems()->count())->toBe(3);
});

test('a file that is not a reading export is rejected kindly', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('other.csv', "name,email\nA,b@example.test");

    $this->actingAs($user)->post(route('import.store'), ['file' => $file])->assertSessionHasErrors('file');
});

test('covers are downloaded once and then served from local storage', function () {
    Storage::fake('local');
    $image = imagecreatetruecolor(40, 60);
    ob_start();
    imagepng($image);
    $png = ob_get_clean();
    Http::fake(['covers.openlibrary.org/*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);
    $book = Book::create(['ol_id' => 'OL-COVER', 'title' => 'Covered', 'cover_url' => 'https://covers.openlibrary.org/b/id/1-M.jpg']);

    $this->get(route('covers.show', $book))->assertOk()->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');
    $this->get(route('covers.show', $book))->assertOk();

    Http::assertSentCount(1);
    Storage::disk('local')->assertExists(CoverCache::relativePath($book));
    expect($book->cover_src)->toContain('/covers/' . $book->id);
});

test('moving a bookmark forward logs a reading day and builds a streak', function () {
    $user = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-STREAK', 'title' => 'Streak Book']);
    $item = UserBookShelf::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'reading', 'current_page' => 10, 'total_pages' => 300]);

    $this->travel(-2)->days();
    $item->update(['current_page' => 30]);
    $this->travelBack();
    $this->travel(-1)->days();
    $item->update(['current_page' => 50]);
    $this->travelBack();
    $item->update(['current_page' => 80]);
    $item->update(['current_page' => 90]); // a second move the same day adds up

    expect(ReadingSession::where('user_id', $user->id)->sum('pages_read'))->toBe(80)
        ->and($user->readingStreak())->toBe(3);
});

test('the year in reading recap shows finished books and can be shared publicly', function () {
    $user = User::factory()->create(['name' => 'Ada Reader']);
    $book = Book::create(['ol_id' => 'OL-YEAR', 'title' => 'Year Book', 'author_name' => 'An Author']);
    UserBookShelf::create(['user_id' => $user->id, 'book_id' => $book->id, 'status' => 'read', 'read_at' => now(), 'total_pages' => 250]);

    $this->actingAs($user)->get(route('year.show', $user->username))
        ->assertOk()->assertSee('Ada’s ' . now()->year, false)->assertSee('Year Book')->assertSee('250');

    auth()->logout();
    $this->get(route('year.show', $user->username))->assertNotFound();
    $this->get(route('share.tree', $user->username))->assertNotFound();

    $user->forceFill(['is_public' => true])->save();
    $this->get(route('share.tree', $user->username))->assertOk()->assertSee('Ada Reader’s living library', false)->assertSee('Year Book');
    $this->get(route('year.show', $user->username))->assertOk();
});

test('readers can start a buddy read, invite a friend, and keep later pages spoiler-hidden', function () {
    $me = User::factory()->create();
    $friend = User::factory()->create();
    $stranger = User::factory()->create();
    $me->following()->attach($friend->id);
    $book = Book::create(['ol_id' => 'OL-BUDDY', 'title' => 'Shared Read']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'want_to_read']);

    $this->actingAs($me)->post(route('buddy.store'), ['book_id' => $book->id, 'invitees' => [$stranger->id]])->assertSessionHasErrors('invitees.0');
    $this->post(route('buddy.store'), ['book_id' => $book->id, 'invitees' => [$friend->id]])->assertRedirect();
    $read = BuddyRead::firstOrFail();
    expect($friend->notifications()->first()->data['kind'])->toBe('buddy')
        ->and($me->shelfItems()->first()->status)->toBe('reading');

    $this->actingAs($stranger)->get(route('buddy.show', $read))->assertNotFound();

    $this->actingAs($friend)->post(route('buddy.join', $read))->assertRedirect(route('buddy.show', $read));
    $this->post(route('buddy.comment', $read), ['body' => 'The twist on page 200!', 'page' => 200])->assertRedirect();

    $this->actingAs($me)->get(route('buddy.show', $read))
        ->assertOk()
        ->assertSee('Spoiler from page 200');
});

test('recommendations suggest what fans of your favourite book loved', function () {
    $me = User::factory()->create();
    $fan = User::factory()->create();
    $loved = Book::create(['ol_id' => 'OL-REC-1', 'title' => 'Loved Book']);
    $suggested = Book::create(['ol_id' => 'OL-REC-2', 'title' => 'You Will Like This']);
    Review::create(['user_id' => $me->id, 'book_id' => $loved->id, 'rating' => 5]);
    Review::create(['user_id' => $fan->id, 'book_id' => $loved->id, 'rating' => 5]);
    Review::create(['user_id' => $fan->id, 'book_id' => $suggested->id, 'rating' => 4.5]);

    $this->actingAs($me)->get(route('books.index'))
        ->assertOk()
        ->assertSee('Because you loved Loved Book')
        ->assertSee('You Will Like This');
});
