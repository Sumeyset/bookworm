<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** What readers say a book feels like, and what else they loved. */
class BookInsights
{
    public const MOODS = [
        'cosy' => '☕ Cosy', 'dark' => '🌑 Dark', 'funny' => '😄 Funny', 'hopeful' => '🌱 Hopeful',
        'emotional' => '💧 Emotional', 'mysterious' => '🔍 Mysterious', 'romantic' => '💞 Romantic',
        'adventurous' => '🧭 Adventurous', 'reflective' => '🪞 Reflective', 'tense' => '⚡ Tense',
        'inspiring' => '✨ Inspiring', 'sad' => '🥀 Sad',
    ];

    public const PACES = ['slow' => 'Slow', 'medium' => 'Medium', 'fast' => 'Fast'];

    /** @return array{moods: Collection<string, int>, pace: Collection<string, int>, voters: int} */
    public static function feel(Book $book): array
    {
        $reviews = Review::where('book_id', $book->id)->get(['moods', 'pace']);
        $moods = $reviews->flatMap(fn ($r) => (array) $r->moods)->filter(fn ($m) => isset(self::MOODS[$m]))->countBy()->sortDesc()->take(5);
        $pace = $reviews->pluck('pace')->filter(fn ($p) => isset(self::PACES[$p]))->countBy();

        return ['moods' => $moods, 'pace' => $pace, 'voters' => $reviews->filter(fn ($r) => $r->moods || $r->pace)->count()];
    }

    /**
     * Readers who loved this book (4★+) also loved: their other 4★+ books,
     * most shared first.
     *
     * @return Collection<int, Book>
     */
    public static function alsoLoved(Book $book, int $limit = 8): Collection
    {
        $fans = Review::where('book_id', $book->id)->where('rating', '>=', 4)->pluck('user_id');
        if ($fans->isEmpty()) {
            return collect();
        }

        $ids = Review::query()
            ->whereIn('user_id', $fans)
            ->where('book_id', '!=', $book->id)
            ->where('rating', '>=', 4)
            ->select('book_id', DB::raw('COUNT(*) as fans'), DB::raw('AVG(rating) as stars'))
            ->groupBy('book_id')
            ->orderByDesc('fans')
            ->orderByDesc('stars')
            ->limit($limit)
            ->pluck('fans', 'book_id');

        return Book::whereIn('id', $ids->keys())->get()
            ->each(fn (Book $b) => $b->setAttribute('fans', $ids[$b->id]))
            ->sortByDesc('fans')
            ->values();
    }
}
