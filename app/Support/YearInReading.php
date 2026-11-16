<?php

namespace App\Support;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;

/** Everything shown on a reader's yearly recap page. */
class YearInReading
{
    public static function for(User $user, int $year): array
    {
        $finished = $user->shelfItems()
            ->with(['book.reviews' => fn ($reviews) => $reviews->where('user_id', $user->id)])
            ->where('status', 'read')
            ->whereYear('read_at', $year)
            ->orderBy('read_at')
            ->get();

        $rated = $finished->map(function ($item) {
            $item->setAttribute('my_rating', $item->book->reviews->first()?->rating);

            return $item;
        });

        $days = $user->readingSessions()
            ->whereYear('read_on', $year)
            ->pluck('read_on')
            ->map(fn ($day) => Carbon::parse($day)->toDateString())
            ->unique()
            ->sort()
            ->values();

        $favouriteQuote = Post::query()
            ->where('user_id', $user->id)
            ->where('type', 'quote')
            ->whereYear('created_at', $year)
            ->withCount('likes')
            ->with('book')
            ->orderByDesc('likes_count')
            ->latest()
            ->first();

        $pages = $finished->sum(fn ($item) => $item->total_pages ?: $item->book->page_count ?: 0);

        return [
            'year' => $year,
            'books' => $finished->count(),
            'pages' => $pages,
            'goal' => $user->yearly_reading_goal ?: 12,
            'reading_days' => $days->count(),
            'longest_streak' => self::longestRun($days),
            'months' => collect(range(1, 12))->map(fn ($m) => $finished->filter(fn ($i) => (int) $i->read_at->format('n') === $m)->count()),
            'busiest_month' => $finished->countBy(fn ($i) => $i->read_at->format('F'))->sortDesc()->keys()->first(),
            'top_rated' => $rated->filter(fn ($i) => $i->my_rating)->sortByDesc('my_rating')->take(5)->values(),
            'longest_book' => $finished->sortByDesc(fn ($i) => $i->total_pages ?: $i->book->page_count ?: 0)->first(),
            'first_book' => $finished->first(),
            'last_book' => $finished->last(),
            'top_authors' => $finished->pluck('book.author_name')->filter()->countBy()->sortDesc()->take(3),
            'average_rating' => round((float) $rated->pluck('my_rating')->filter()->avg(), 1),
            'quote' => $favouriteQuote,
            'covers' => $finished->pluck('book')->filter(fn ($book) => $book->cover_url)->take(24),
            'authors_count' => $finished->pluck('book.author_name')->filter()->unique()->count(),
            'badges' => $user->badges()->whereYear('created_at', $year)->latest()->get(),
            'personality' => self::personality($finished, $pages),
        ];
    }

    /** A playful reading type for the year story: [name, emoji, line]. */
    private static function personality($finished, int $pages): array
    {
        $count = $finished->count();
        if ($count === 0) {
            return ['The Dreamer', '🌙', 'Your best reading year is still ahead of you.'];
        }
        $average = $pages / $count;
        $authors = $finished->pluck('book.author_name')->filter()->unique()->count();
        $old = $finished->filter(fn ($i) => $i->book?->first_publish_year && $i->book->first_publish_year < 1950)->count();

        return match (true) {
            $old >= max(2, $count / 3) => ['The Time Traveller', '🕰️', 'You spent the year with the classics.'],
            $average >= 420 => ['The Marathoner', '🏔️', 'Big books never scared you.'],
            $count >= 20 && $average < 300 => ['The Sprinter', '⚡', 'Book after book after book.'],
            $authors >= max(3, $count * .8) => ['The Explorer', '🧭', 'A new voice with almost every book.'],
            default => ['The Devoted Reader', '📖', 'Steady, curious and always mid-chapter.'],
        };
    }

    private static function longestRun($days): int
    {
        $best = 0;
        $run = 0;
        $previous = null;
        foreach ($days as $day) {
            $date = Carbon::parse($day);
            $run = $previous && $previous->copy()->addDay()->isSameDay($date) ? $run + 1 : 1;
            $best = max($best, $run);
            $previous = $date;
        }

        return $best;
    }
}
