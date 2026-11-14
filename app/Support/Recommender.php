<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Book suggestions, newest signal first:
 *  - "Because you loved X": what readers who also loved X rated highly
 *  - "Loved by readers you follow"
 *  - "Popular on Bookworm" lately
 *  - picks for your favourite genres (Open Library subjects)
 */
class Recommender
{
    private const PER_ROW = 8;

    /** @return list<array{title: string, subtitle: string, books: Collection}> */
    public static function for(User $user, bool $includeGenres = true): array
    {
        $onShelf = $user->shelfItems()->pluck('book_id');
        $sections = [];

        $seed = Review::query()
            ->where('user_id', $user->id)
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderByDesc('rating')
            ->latest()
            ->first();

        if ($seed) {
            $fans = Review::query()
                ->where('book_id', $seed->book_id)
                ->where('rating', '>=', 4)
                ->where('user_id', '!=', $user->id)
                ->pluck('user_id');
            $books = self::topRated($fans, $onShelf->push($seed->book_id));
            if ($books->isNotEmpty()) {
                $sections[] = ['title' => 'Because you loved ' . $seed->book->title, 'subtitle' => 'Readers who adored it also rated these highly.', 'books' => $books];
            }
        }

        $circle = self::topRated($user->following()->pluck('users.id'), $onShelf);
        if ($circle->isNotEmpty()) {
            $sections[] = ['title' => 'Loved by readers you follow', 'subtitle' => 'Four stars and up from your reading circle.', 'books' => $circle];
        }

        $popularIds = UserBookShelf::query()
            ->where('created_at', '>=', now()->subDays(90))
            ->whereNotIn('book_id', $onShelf)
            ->where('user_id', '!=', $user->id)
            ->selectRaw('book_id, COUNT(*) as readers')
            ->groupBy('book_id')
            ->orderByDesc('readers')
            ->limit(self::PER_ROW)
            ->pluck('readers', 'book_id');
        if ($popularIds->isNotEmpty()) {
            $books = Book::whereIn('id', $popularIds->keys())->get()->sortByDesc(fn ($book) => $popularIds[$book->id])->values();
            $sections[] = ['title' => 'Popular on Bookworm', 'subtitle' => 'What readers have been shelving lately.', 'books' => $books];
        }

        if ($includeGenres) {
            foreach (array_slice($user->preferred_genres ?? [], 0, 2) as $genre) {
                $books = self::forGenre($genre)->reject(fn ($book) => $onShelf->contains($book->id))->take(self::PER_ROW)->values();
                if ($books->isNotEmpty()) {
                    $sections[] = ['title' => 'Picks for ' . Str::lower($genre) . ' lovers', 'subtitle' => 'Well-loved ' . Str::lower($genre) . ' from Open Library.', 'books' => $books];
                }
            }
        }

        return $sections;
    }

    private static function topRated(Collection $userIds, Collection $exclude): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        $scores = Review::query()
            ->whereIn('user_id', $userIds)
            ->where('rating', '>=', 4)
            ->whereNotIn('book_id', $exclude)
            ->selectRaw('book_id, COUNT(*) as fans, AVG(rating) as average')
            ->groupBy('book_id')
            ->orderByDesc('fans')
            ->orderByDesc('average')
            ->limit(self::PER_ROW)
            ->get()
            ->keyBy('book_id');

        return Book::whereIn('id', $scores->keys())->get()
            ->sortByDesc(fn ($book) => [$scores[$book->id]->fans, $scores[$book->id]->average])
            ->values();
    }

    /** Books for a genre from Open Library, cached for a day and saved to our books table. */
    public static function forGenre(string $genre): Collection
    {
        $works = Cache::remember('ol:subject:' . Str::slug($genre), now()->addDay(), function () use ($genre) {
            try {
                return Http::timeout(4)->connectTimeout(2)
                    ->get('https://openlibrary.org/subjects/' . Str::slug(Str::lower($genre), '_') . '.json', ['limit' => 12])
                    ->throw()
                    ->json('works', []);
            } catch (Throwable $exception) {
                report($exception);

                return [];
            }
        });

        $rows = collect($works)
            ->filter(fn ($work) => ! empty($work['key']) && ! empty($work['title']))
            ->map(fn ($work) => [
                'ol_id' => str_replace('/works/', '', $work['key']),
                'title' => Str::limit($work['title'], 250, ''),
                'author_name' => isset($work['authors'][0]['name']) ? Str::limit($work['authors'][0]['name'], 250, '') : null,
                'cover_url' => ! empty($work['cover_id']) ? 'https://covers.openlibrary.org/b/id/' . $work['cover_id'] . '-M.jpg' : null,
                'first_publish_year' => $work['first_publish_year'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->unique('ol_id')
            ->values();

        if ($rows->isEmpty()) {
            return collect();
        }

        Book::upsert($rows->all(), ['ol_id'], ['title', 'author_name', 'cover_url', 'first_publish_year']);

        return Book::whereIn('ol_id', $rows->pluck('ol_id'))->get();
    }
}
