<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Imports a reading export into a reader's shelf: books, shelf status,
 * ratings, reviews, page counts and finish dates.
 *
 * Understands Goodreads, StoryGraph and LibraryThing exports, and any other
 * spreadsheet (CSV or tab-separated) with a title column. preview() shows
 * what would happen without saving anything; run() does it.
 */
class ReadingImport
{
    public const MAX_ROWS = 5000;

    public const SOURCES = [
        'goodreads' => 'Goodreads',
        'storygraph' => 'StoryGraph',
        'librarything' => 'LibraryThing',
        'csv' => 'your spreadsheet',
    ];

    /** Which export this is (throws a friendly error if it isn't one). */
    public static function detectFile(string $path): string
    {
        [$handle, , $source] = self::open($path);
        fclose($handle);

        return $source;
    }

    /**
     * What an import would do, without saving anything.
     *
     * @return array{source: string, total: int, new: int, duplicates: int, skipped: int, ratings: int, reviews: int, statuses: array<string, int>, sample: list<array{title: string, author: ?string, status: string, rating: ?float}>}
     */
    public static function preview(User $user, string $path): array
    {
        [$handle, $header, $source, $delimiter] = self::open($path);
        $owned = $user->shelfItems()->with('book:id,isbn,ol_id,title,author_name')->get()->pluck('book')->filter();
        $ownedIsbns = $owned->pluck('isbn')->filter()->flip();
        $ownedKeys = $owned->map(fn ($b) => Str::lower($b->title . '|' . $b->author_name))->flip();

        $preview = ['source' => $source, 'total' => 0, 'new' => 0, 'duplicates' => 0, 'skipped' => 0, 'ratings' => 0, 'reviews' => 0,
            'statuses' => ['read' => 0, 'reading' => 0, 'want_to_read' => 0], 'sample' => []];
        $seen = [];

        foreach (self::rows($handle, $header, $source, $delimiter) as $data) {
            $preview['total']++;
            if (! $data) {
                $preview['skipped']++;
                continue;
            }
            $key = Str::lower($data['title'] . '|' . $data['author']);
            if (($data['isbn'] && $ownedIsbns->has($data['isbn'])) || $ownedKeys->has($key) || isset($seen[$data['isbn'] ?: $key])) {
                $preview['duplicates']++;
                continue;
            }
            $seen[$data['isbn'] ?: $key] = true;
            $preview['new']++;
            $preview['statuses'][$data['status']]++;
            $preview['ratings'] += $data['rating'] ? 1 : 0;
            $preview['reviews'] += $data['review'] ? 1 : 0;
            if (count($preview['sample']) < 8) {
                $preview['sample'][] = ['title' => $data['title'], 'author' => $data['author'], 'status' => $data['status'], 'rating' => $data['rating']];
            }
        }
        fclose($handle);

        return $preview;
    }

    /** @return array{source: string, imported: int, skipped: int, reviews: int} */
    public static function run(User $user, string $path): array
    {
        [$handle, $header, $source, $delimiter] = self::open($path);

        $stats = ['source' => $source, 'imported' => 0, 'skipped' => 0, 'reviews' => 0];
        $existing = $user->shelfItems()->pluck('book_id')->flip();

        // Bulk import: don't notify followers for every single historic book or review.
        FriendActivity::$quiet = true;
        Acorns::$quiet = true;
        try {
            Review::withoutEvents(function () use ($handle, $header, $source, $delimiter, $user, &$stats, $existing) {
                DB::transaction(function () use ($handle, $header, $source, $delimiter, $user, &$stats, $existing) {
                    foreach (self::rows($handle, $header, $source, $delimiter) as $data) {
                        if (! $data) {
                            $stats['skipped']++;
                            continue;
                        }

                        $book = self::findOrCreateBook($data);
                        if ($existing->has($book->id)) {
                            $stats['skipped']++;
                            continue;
                        }
                        $existing->put($book->id, true);

                        UserBookShelf::create([
                            'user_id' => $user->id,
                            'book_id' => $book->id,
                            'status' => $data['status'],
                            'total_pages' => $data['pages'],
                            'current_page' => $data['status'] === 'read' ? $data['pages'] : null,
                            'read_at' => $data['status'] === 'read' ? ($data['read_at'] ?? $data['added_at'] ?? now()) : null,
                        ]);
                        $stats['imported']++;

                        if ($data['rating']) {
                            Review::updateOrCreate(
                                ['user_id' => $user->id, 'book_id' => $book->id],
                                ['rating' => $data['rating'], 'body' => $data['review']],
                            );
                            $stats['reviews']++;
                        }
                    }
                });
            });
        } finally {
            FriendActivity::$quiet = false;
            Acorns::$quiet = false;
        }

        fclose($handle);

        return $stats;
    }

    /** @return array{0: resource, 1: list<string>, 2: string, 3: string} [handle, header, source, delimiter] */
    private static function open(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException('That file could not be read.');
        }

        // Comma or tab separated? Look at the first line.
        $first = (string) fgets($handle);
        rewind($handle);
        $delimiter = substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',';

        $header = fgetcsv($handle, null, $delimiter);
        if (! $header || $header === [null]) {
            fclose($handle);
            throw new RuntimeException('That file looks empty.');
        }
        $header = array_map(fn ($h) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), $header);

        try {
            $source = self::detect($header);
        } catch (RuntimeException $e) {
            fclose($handle);
            throw $e;
        }

        return [$handle, $header, $source, $delimiter];
    }

    /** Each row as import data (null for a row that can't be used). */
    private static function rows($handle, array $header, string $source, string $delimiter): \Generator
    {
        $rows = 0;
        while (($values = fgetcsv($handle, null, $delimiter)) !== false && $rows++ < self::MAX_ROWS) {
            if (count($values) < 2) {
                continue;
            }
            $row = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), ''));
            $data = match ($source) {
                'goodreads' => self::fromGoodreads($row),
                'storygraph' => self::fromStoryGraph($row),
                default => self::fromSpreadsheet($row),
            };
            yield ($data && $data['title'] !== '') ? $data : null;
        }
    }

    private static function detect(array $header): string
    {
        if (in_array('Exclusive Shelf', $header, true) && in_array('Title', $header, true)) {
            return 'goodreads';
        }
        if (in_array('Read Status', $header, true) && in_array('Title', $header, true)) {
            return 'storygraph';
        }
        if (in_array('Primary Author', $header, true) && in_array('Title', $header, true)) {
            return 'librarything';
        }
        if (self::column(array_combine($header, $header), self::TITLE_COLUMNS) !== null) {
            return 'csv';
        }

        throw new RuntimeException('We couldn’t find a “Title” column in this file. Upload a Goodreads, StoryGraph or LibraryThing export, or a spreadsheet with a Title column.');
    }

    private const TITLE_COLUMNS = ['title', 'book title', 'book', 'book name', 'name of book'];

    /** First non-empty value among columns with these names (any case). */
    private static function column(array $row, array $names): ?string
    {
        foreach ($row as $key => $value) {
            if (in_array(Str::lower(trim((string) $key)), $names, true) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    /** LibraryThing, and any other spreadsheet with a Title column. */
    private static function fromSpreadsheet(array $row): ?array
    {
        $title = self::column($row, self::TITLE_COLUMNS) ?? '';
        $author = self::column($row, ['author', 'authors', 'primary author', 'author l-f', 'writer', 'by']);
        $readAt = self::date(self::column($row, ['date read', 'date finished', 'finished', 'last date read', 'read date', 'date completed']));
        $statusText = Str::lower((string) self::column($row, ['status', 'shelf', 'exclusive shelf', 'read status', 'collections', 'bookshelves', 'state']));
        $rating = (float) self::column($row, ['rating', 'my rating', 'star rating', 'stars', 'score']);
        if ($rating > 5 && $rating <= 10) {
            $rating /= 2; // out of 10
        }

        return [
            'key' => null,
            'title' => $title,
            'author' => $author ? trim(explode(';', $author)[0]) : null,
            'isbn' => self::cleanIsbn(self::column($row, ['isbn13', 'isbn', 'isbn/uid', 'isbns', 'isbn 13', 'isbn-13']) ?? ''),
            'year' => self::year(self::column($row, ['year', 'original publication year', 'year published', 'publication year', 'date']) ?? ''),
            'pages' => self::int(self::column($row, ['pages', 'page count', 'number of pages', 'page_count', 'length'])),
            'status' => match (true) {
                $statusText !== '' && (str_contains($statusText, 'to read') || str_contains($statusText, 'to-read') || str_contains($statusText, 'want') || str_contains($statusText, 'wish') || str_contains($statusText, 'tbr')) => 'want_to_read',
                str_contains($statusText, 'current') || str_contains($statusText, 'reading now') || $statusText === 'reading' => 'reading',
                str_contains($statusText, 'read') || str_contains($statusText, 'finished') || str_contains($statusText, 'done') || $readAt !== null => 'read',
                default => 'want_to_read',
            },
            'rating' => self::rating($rating),
            'review' => self::review(self::column($row, ['review', 'my review', 'notes', 'comment'])),
            'read_at' => $readAt,
            'added_at' => self::date(self::column($row, ['date added', 'added', 'entry date', 'acquired'])),
        ];
    }

    private static function fromGoodreads(array $row): ?array
    {
        $isbn = self::cleanIsbn($row['ISBN13'] ?? '') ?: self::cleanIsbn($row['ISBN'] ?? '');

        return [
            'key' => ! empty($row['Book Id']) ? 'gr:' . $row['Book Id'] : null,
            'title' => trim((string) ($row['Title'] ?? '')),
            'author' => trim((string) ($row['Author'] ?? '')) ?: null,
            'isbn' => $isbn,
            'year' => self::year($row['Original Publication Year'] ?? '') ?? self::year($row['Year Published'] ?? ''),
            'pages' => self::int($row['Number of Pages'] ?? null),
            'status' => match (trim((string) ($row['Exclusive Shelf'] ?? ''))) {
                'read' => 'read',
                'currently-reading' => 'reading',
                default => 'want_to_read',
            },
            'rating' => self::rating($row['My Rating'] ?? null),
            'review' => self::review($row['My Review'] ?? null),
            'read_at' => self::date($row['Date Read'] ?? null),
            'added_at' => self::date($row['Date Added'] ?? null),
        ];
    }

    private static function fromStoryGraph(array $row): ?array
    {
        return [
            'key' => null,
            'title' => trim((string) ($row['Title'] ?? '')),
            'author' => trim(explode(',', (string) ($row['Authors'] ?? ''))[0]) ?: null,
            'isbn' => self::cleanIsbn($row['ISBN/UID'] ?? ''),
            'year' => null,
            'pages' => self::int($row['Pages'] ?? null),
            'status' => match (trim((string) ($row['Read Status'] ?? ''))) {
                'read' => 'read',
                'currently-reading' => 'reading',
                default => 'want_to_read',
            },
            'rating' => self::rating($row['Star Rating'] ?? null),
            'review' => self::review($row['Review'] ?? null),
            'read_at' => self::date($row['Last Date Read'] ?? null),
            'added_at' => self::date($row['Date Added'] ?? null),
        ];
    }

    private static function findOrCreateBook(array $data): Book
    {
        $book = null;
        if ($data['isbn']) {
            $book = Book::where('isbn', $data['isbn'])->first();
        }
        $olId = $data['isbn'] ? 'isbn:' . $data['isbn'] : ($data['key'] ?? 'import:' . md5(Str::lower($data['title'] . '|' . $data['author'])));
        $book ??= Book::where('ol_id', $olId)->first();

        return $book ?? Book::create([
            'ol_id' => $olId,
            'isbn' => $data['isbn'],
            'title' => Str::limit($data['title'], 250, ''),
            'author_name' => $data['author'] ? Str::limit($data['author'], 250, '') : null,
            'first_publish_year' => $data['year'],
            'page_count' => $data['pages'],
            'cover_url' => $data['isbn'] ? 'https://covers.openlibrary.org/b/isbn/' . $data['isbn'] . '-M.jpg' : null,
        ]);
    }

    private static function cleanIsbn(?string $value): ?string
    {
        $clean = strtoupper(preg_replace('/[^0-9Xx]/', '', (string) $value));

        return in_array(strlen($clean), [10, 13], true) ? $clean : null;
    }

    private static function int(mixed $value): ?int
    {
        $number = (int) preg_replace('/\D/', '', (string) $value);

        return $number > 0 && $number < 100000 ? $number : null;
    }

    private static function year(mixed $value): ?int
    {
        $year = (int) $value;

        return $year > 0 && $year < 65535 ? $year : null;
    }

    private static function rating(mixed $value): ?float
    {
        $rating = round(((float) $value) * 2) / 2;

        return $rating >= 0.5 && $rating <= 5 ? $rating : null;
    }

    private static function review(mixed $value): ?string
    {
        $text = trim(strip_tags(str_replace(['<br/>', '<br />', '<br>'], "\n", (string) $value)));

        return $text !== '' ? Str::limit($text, 3000, '') : null;
    }

    private static function date(mixed $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse(str_replace('/', '-', $value));
        } catch (Throwable) {
            return null;
        }
    }
}
