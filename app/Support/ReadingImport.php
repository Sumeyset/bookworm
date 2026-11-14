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
 * Imports a Goodreads or StoryGraph CSV export into a reader's tree:
 * books, shelf status, ratings, reviews, page counts and finish dates.
 */
class ReadingImport
{
    public const MAX_ROWS = 5000;

    /** @return array{source: string, imported: int, skipped: int, reviews: int} */
    public static function run(User $user, string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException('That file could not be read.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            throw new RuntimeException('That file looks empty.');
        }
        $header = array_map(fn ($h) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), $header);
        $source = self::detect($header);

        $stats = ['source' => $source, 'imported' => 0, 'skipped' => 0, 'reviews' => 0];
        $existing = $user->shelfItems()->pluck('book_id')->flip();

        // Bulk import: don't notify followers for every single historic book or review.
        FriendActivity::$quiet = true;
        try {
            Review::withoutEvents(function () use ($handle, $header, $source, $user, &$stats, $existing) {
                DB::transaction(function () use ($handle, $header, $source, $user, &$stats, $existing) {
                    $rows = 0;
                    while (($values = fgetcsv($handle)) !== false && $rows++ < self::MAX_ROWS) {
                        if (count($values) < 2) {
                            continue;
                        }
                        $row = array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), ''));
                        $data = $source === 'goodreads' ? self::fromGoodreads($row) : self::fromStoryGraph($row);
                        if (! $data || $data['title'] === '') {
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
        }

        fclose($handle);

        return $stats;
    }

    private static function detect(array $header): string
    {
        if (in_array('Exclusive Shelf', $header, true) && in_array('Title', $header, true)) {
            return 'goodreads';
        }
        if (in_array('Read Status', $header, true) && in_array('Title', $header, true)) {
            return 'storygraph';
        }

        throw new RuntimeException('This doesn’t look like a Goodreads or StoryGraph export. Please upload the CSV file they give you.');
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
