<?php

namespace App\Support\Barnaby;

use App\Models\Book;
use App\Models\BookPdfHighlight;
use App\Models\BuddyRead;
use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Barnaby the worm: Bookworm's reading assistant.
 *
 * What he sees: your shelf (titles, status, ratings), the book or passage you
 * ask about, and your own highlights for a recap. Never private messages.
 */
class Barnaby
{
    private const PERSONA = <<<'TXT'
You are Barnaby, a friendly, bookish worm who lives inside books and helps readers on Bookworm, a reading app.
Personality: warm, curious, a little playful. An occasional gentle worm or book pun is welcome, but not in every message.
Style: short and clear. Usually under 150 words. Use plain text with **bold** for book titles and short "- " bullet lists when listing. No headings, no tables.
Honesty: never make up facts about the reader. Only recommend real, published books. If you don't know a book well, say so instead of guessing.
Spoilers: never reveal plot points beyond what the reader has reached. When unsure whether something is a spoiler, leave it out.
Stay on books and reading; politely steer other topics back to books.
TXT;

    public function __construct(private BarnabyClient $client)
    {
    }

    /* ---------------- limits ---------------- */

    /** @return array{0: bool, 1: string|null} [allowed, reason] */
    public function allow(User $user): array
    {
        $minute = 'barnaby:min:' . $user->id;
        $day = 'barnaby:day:' . $user->id . ':' . now()->toDateString();

        if (RateLimiter::tooManyAttempts($minute, config('barnaby.per_minute'))) {
            return [false, 'Barnaby needs a moment to turn the page — try again in a few seconds.'];
        }
        if (RateLimiter::tooManyAttempts($day, config('barnaby.per_day'))) {
            return [false, 'Barnaby has read himself sleepy today 😴 He’ll be back tomorrow.'];
        }
        RateLimiter::hit($minute, 60);
        RateLimiter::hit($day, 86400);

        return [true, null];
    }

    public function remainingToday(User $user): int
    {
        return RateLimiter::remaining('barnaby:day:' . $user->id . ':' . now()->toDateString(), config('barnaby.per_day'));
    }

    /* ---------------- chat & recommendations ---------------- */

    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array{reply: string, books: array<int, array<string, mixed>>}
     */
    public function chat(User $user, string $message, array $history = [], ?Book $book = null, ?int $page = null): array
    {
        $shelf = $this->shelfSummary($user);
        $system = self::PERSONA . "\n\nThe reader's name is {$this->firstName($user)}.\n"
            . ($shelf !== '' ? "Their Bookworm shelf (title — author [status, their rating]):\n{$shelf}\n" : "Their shelf is empty so far.\n")
            . ($book ? "They are looking at **{$book->title}**" . ($book->author_name ? " by {$book->author_name}" : '') . ($page ? ", and are on page {$page} — no spoilers past it" : '') . ".\n" : '')
            . <<<'TXT'

When you recommend specific books, recommend 3 to 5 real books the reader doesn't already have on their shelf, and pick them to fit what they described or what they've loved.
After your friendly message, add a machine-readable list in exactly this format (it is hidden from the reader and turned into cards):
```books
[{"title": "Book Title", "author": "Author Name", "why": "One short sentence on why it fits."}]
```
Only include the block when you are actually recommending books.
TXT;

        $turns = collect($history)
            ->filter(fn ($turn) => in_array($turn['role'] ?? '', ['user', 'assistant'], true) && filled($turn['text'] ?? null))
            ->take(-10)
            ->map(fn ($turn) => ['role' => $turn['role'], 'text' => Str::limit((string) $turn['text'], 1500)])
            ->values()
            ->push(['role' => 'user', 'text' => $message])
            ->all();

        $raw = $this->client->reply($system, $turns, 0.8, 900);
        [$reply, $picks] = $this->splitBooks($raw);

        $onShelf = $user->shelfItems()->with('book:id,title')->get()->pluck('book.title')->filter()->map(fn ($title) => Str::lower($title));
        $books = $this->findBooks(collect($picks)->reject(fn ($pick) => $onShelf->contains(Str::lower($pick['title'])))->take(5)->values()->all(), $user);

        return ['reply' => $reply !== '' ? $reply : 'Here are a few I think you’ll love:', 'books' => $books];
    }

    /* ---------------- reader helpers ---------------- */

    public function explain(Book $book, string $passage, ?int $page): string
    {
        $passage = Str::limit(trim($passage), 1500);

        return Cache::remember('barnaby:explain:' . md5($book->id . '|' . $passage), now()->addWeek(), fn () => $this->client->reply(
            self::PERSONA,
            [['role' => 'user', 'text' => "I'm reading **{$book->title}**" . ($book->author_name ? " by {$book->author_name}" : '') . ($page ? ", on page {$page}" : '') . ".\n"
                . "Help me understand this passage. Explain what it means in plain words, and any unusual words, references or context. Don't spoil anything that comes later.\n\n\"{$passage}\""]],
            0.4,
            600,
        ));
    }

    public function recap(User $user, Book $book, int $page, ?int $totalPages = null): string
    {
        // The reader's own highlights up to their bookmark help Barnaby (and keep him honest).
        $highlights = BookPdfHighlight::query()
            ->where('user_id', $user->id)
            ->whereHas('bookPdf', fn ($pdfs) => $pdfs->where('book_id', $book->id))
            ->where('page_number', '<=', $page)
            ->orderBy('page_number')
            ->take(15)
            ->get(['page_number', 'text'])
            ->map(fn ($highlight) => "- p. {$highlight->page_number}: \"" . Str::limit($highlight->text, 220) . '"')
            ->implode("\n");

        $key = 'barnaby:recap:' . md5($user->id . '|' . $book->id . '|' . $page . '|' . $highlights);

        return Cache::remember($key, now()->addDay(), fn () => $this->client->reply(
            self::PERSONA,
            [['role' => 'user', 'text' => "Give me a short \"story so far\" recap of **{$book->title}**" . ($book->author_name ? " by {$book->author_name}" : '')
                . " up to page {$page}" . ($totalPages ? " of {$totalPages}" : '') . ". I'm picking it back up.\n"
                . "STRICT: nothing that happens after page {$page}. Main characters and where things stand, in 4–7 short bullets. "
                . "If you don't know this book well enough to be sure what happens by that page, say so honestly and give a spoiler-free reminder of the premise instead."
                . ($highlights !== '' ? "\n\nLines I highlighted so far (use them as anchors):\n{$highlights}" : '')]],
            0.3,
            700,
        ));
    }

    public function discussion(BuddyRead $read, int $page): string
    {
        $book = $read->book;

        return Cache::remember('barnaby:discuss:' . md5($book->id . '|' . $page), now()->addDay(), fn () => $this->client->reply(
            self::PERSONA,
            [['role' => 'user', 'text' => "We're a buddy-read group reading **{$book->title}**" . ($book->author_name ? " by {$book->author_name}" : '')
                . ". Everyone has reached at least page {$page}. Give us 5 discussion questions to talk about, numbered 1–5, each one or two sentences. "
                . "STRICT: nothing that happens after page {$page}. Mix feelings, characters, themes and predictions. "
                . "If you don't know the book well, write good general questions that fit any reader at this point."]],
            0.8,
            600,
        ));
    }

    /* ---------------- helpers ---------------- */

    private function firstName(User $user): string
    {
        return Str::before($user->name, ' ') ?: $user->name;
    }

    private function shelfSummary(User $user): string
    {
        $ratings = $user->reviews()->pluck('rating', 'book_id');

        return $user->shelfItems()
            ->with('book:id,title,author_name')
            ->latest('updated_at')
            ->take(40)
            ->get()
            ->filter(fn ($item) => $item->book)
            ->map(fn ($item) => '- ' . $item->book->title . ($item->book->author_name ? ' — ' . $item->book->author_name : '')
                . ' [' . (UserBookShelf::STATUSES[$item->status] ?? $item->status)
                . (isset($ratings[$item->book_id]) ? ', ' . rtrim(rtrim((string) $ratings[$item->book_id], '0'), '.') . '★' : '') . ']')
            ->implode("\n");
    }

    /** Pull the hidden ```books [...]``` block out of a reply. */
    public function splitBooks(string $raw): array
    {
        $books = [];
        $reply = $raw;
        if (preg_match('/```(?:books|json)\s*(\[.*?\])\s*```/s', $raw, $match)) {
            $books = json_decode($match[1], true) ?: [];
            $reply = trim(str_replace($match[0], '', $raw));
        }

        $books = collect($books)
            ->filter(fn ($book) => is_array($book) && filled($book['title'] ?? null))
            ->map(fn ($book) => [
                'title' => Str::limit(trim((string) $book['title']), 200, ''),
                'author' => filled($book['author'] ?? null) ? Str::limit(trim((string) $book['author']), 120, '') : null,
                'why' => Str::limit(trim((string) ($book['why'] ?? '')), 240),
            ])
            ->unique(fn ($book) => Str::lower($book['title']))
            ->values()
            ->all();

        return [$reply, $books];
    }

    /**
     * Look the recommendations up on Open Library (in parallel) so each card
     * gets a cover and opens a real book page on Bookworm.
     */
    private function findBooks(array $picks, User $user): array
    {
        if ($picks === []) {
            return [];
        }

        $responses = Http::pool(fn ($pool) => collect($picks)->map(fn ($pick, $index) => $pool->as((string) $index)
            ->timeout(6)->connectTimeout(3)
            ->get('https://openlibrary.org/search.json', array_filter([
                'title' => $pick['title'],
                'author' => $pick['author'],
                'limit' => 1,
                'fields' => 'key,title,author_name,first_publish_year,cover_i',
            ])))->all());

        $shelfBookIds = $user->shelfItems()->pluck('book_id')->flip();

        return collect($picks)->map(function ($pick, $index) use ($responses, $shelfBookIds) {
            $response = $responses[(string) $index] ?? null;
            $doc = $response instanceof \Illuminate\Http\Client\Response && $response->successful() ? $response->json('docs.0') : null;
            $book = null;
            if ($doc && ! empty($doc['key'])) {
                $book = Book::updateOrCreate(
                    ['ol_id' => str_replace('/works/', '', $doc['key'])],
                    array_filter([
                        'title' => Str::limit($doc['title'] ?? $pick['title'], 250, ''),
                        'author_name' => isset($doc['author_name'][0]) ? Str::limit($doc['author_name'][0], 250, '') : $pick['author'],
                        'cover_url' => isset($doc['cover_i']) ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-M.jpg" : null,
                        'first_publish_year' => $doc['first_publish_year'] ?? null,
                    ], fn ($value) => $value !== null),
                );
            }

            return [
                'title' => $book?->title ?? $pick['title'],
                'author' => $book?->author_name ?? $pick['author'],
                'why' => $pick['why'],
                'cover' => $book?->cover_url ? $book->cover_src : null,
                'year' => $book?->first_publish_year,
                'url' => $book ? route('books.show', $book) : route('books.index', ['q' => trim($pick['title'] . ' ' . $pick['author'])]),
                'on_shelf' => $book ? $shelfBookIds->has($book->id) : false,
            ];
        })->all();
    }
}
