<?php

namespace App\Support;

use App\Models\Post;
use App\Models\ReadingSession;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBadge;
use App\Notifications\BookwormNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Monthly reading challenges and badges.
 *
 * Every month four challenges are picked from POOL (the same four for everyone).
 * Progress is worked out from what readers already log, so nothing extra is
 * tracked. Finishing one pays acorns and leaves a badge on the profile.
 * Lifetime milestones (MILESTONES) give badges too.
 */
class Challenges
{
    public const PER_MONTH = 4;

    /** key => [name, emoji, description, acorns, goal] */
    public const POOL = [
        'chunky' => ['Chunky book', '📚', 'Finish a book with more than 400 pages', 60, 1],
        'short' => ['Quick read', '⚡', 'Finish a book under 200 pages', 40, 1],
        'classic' => ['Classic', '🏛️', 'Finish a book first published before 1950', 60, 1],
        'three' => ['Hat trick', '🎩', 'Finish 3 books this month', 80, 3],
        'five' => ['Bookworm month', '🐛', 'Finish 5 books this month', 150, 5],
        'new_author' => ['New voice', '🌱', 'Finish a book by an author you haven’t read before', 50, 1],
        'days' => ['Steady reader', '📅', 'Read on 15 different days this month', 100, 15],
        'pages' => ['Page turner', '📖', 'Read 1,000 pages this month', 120, 1000],
        'reviews' => ['Critic', '✍️', 'Write 2 reviews this month', 50, 2],
        'quote' => ['Quote keeper', '❝', 'Share a quote this month', 30, 1],
        'together' => ['Reading together', '🤝', 'Join a buddy read or a book club', 40, 1],
    ];

    /** key => [name, emoji, description] — lifetime badges */
    public const MILESTONES = [
        'books_1' => ['First book', '🌱', 'Finished a first book on Bookworm'],
        'books_10' => ['Ten books', '🔟', 'Finished 10 books'],
        'books_50' => ['Fifty books', '🏅', 'Finished 50 books'],
        'books_100' => ['Hundred books', '🏆', 'Finished 100 books'],
        'streak_7' => ['Week streak', '🔥', 'Read 7 days in a row'],
        'streak_30' => ['Month streak', '🌟', 'Read 30 days in a row'],
        'streak_100' => ['Hundred-day streak', '💎', 'Read 100 days in a row'],
        'reviews_10' => ['Reviewer', '🖋️', 'Wrote 10 reviews'],
        'challenges_10' => ['Challenger', '🎯', 'Completed 10 monthly challenges'],
    ];

    /** The challenge keys for a month: a steady pick, different every month. */
    public static function forMonth(?CarbonInterface $month = null): array
    {
        $ym = ($month ?? now())->format('Y-m');
        $keys = array_keys(self::POOL);
        usort($keys, fn ($a, $b) => crc32($ym . $a) <=> crc32($ym . $b));
        $picked = array_slice($keys, 0, self::PER_MONTH);
        // Never both "3 books" and "5 books" in one month.
        if (in_array('three', $picked, true) && in_array('five', $picked, true)) {
            $picked[array_search('five', $picked, true)] = $keys[self::PER_MONTH];
        }

        return $picked;
    }

    /**
     * This month's challenges with the reader's progress.
     *
     * @return Collection<int, array{key: string, name: string, emoji: string, description: string, acorns: int, goal: int, progress: int, done: bool}>
     */
    public static function progress(User $user, ?CarbonInterface $month = null): Collection
    {
        $month = Carbon::instance($month ?? now());
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();
        $finished = null;

        return collect(self::forMonth($month))->map(function (string $key) use ($user, $from, $to, &$finished, $month) {
            [$name, $emoji, $description, $acorns, $goal] = self::POOL[$key];
            $finished ??= self::finished($user, $from, $to);
            $progress = min($goal, self::measure($key, $user, $from, $to, $finished));

            return [
                'key' => $key,
                'name' => $name,
                'emoji' => $emoji,
                'description' => $description,
                'acorns' => $acorns,
                'goal' => $goal,
                'progress' => $progress,
                'done' => $progress >= $goal,
                'badge' => self::badgeKey($month, $key),
            ];
        });
    }

    /** Award anything newly earned: this month's challenges and lifetime badges. */
    public static function check(User $user): void
    {
        if (Acorns::$quiet || FriendActivity::$quiet) {
            return;
        }

        $owned = $user->badges()->pluck('key')->flip();

        foreach (self::progress($user) as $challenge) {
            if ($challenge['done'] && ! $owned->has($challenge['badge'])) {
                if (self::give($user, $challenge['badge'], $challenge['name'], $challenge['emoji'], now()->format('F Y') . ' challenge: ' . $challenge['description'])) {
                    Acorns::award($user, $challenge['acorns'], 'challenge', $challenge['badge'], 'for the “' . $challenge['name'] . '” challenge');
                    $owned[$challenge['badge']] = true;
                }
            }
        }

        foreach (self::milestonesReached($user, $owned) as $key) {
            [$name, $emoji, $description] = self::MILESTONES[$key];
            self::give($user, $key, $name, $emoji, $description);
        }
    }

    /** @return list<string> milestone keys reached but not yet owned */
    private static function milestonesReached(User $user, Collection $owned): array
    {
        $missing = array_filter(array_keys(self::MILESTONES), fn ($key) => ! $owned->has($key));
        if (! $missing) {
            return [];
        }

        $books = $user->shelfItems()->where('status', 'read')->count();
        $streak = $user->readingStreak();
        $reviews = $user->reviews()->whereNotNull('body')->where('body', '!=', '')->count();
        $challenges = $owned->keys()->filter(fn ($key) => str_starts_with($key, 'challenge:'))->count();

        return array_values(array_filter($missing, fn ($key) => match ($key) {
            'books_1' => $books >= 1,
            'books_10' => $books >= 10,
            'books_50' => $books >= 50,
            'books_100' => $books >= 100,
            'streak_7' => $streak >= 7,
            'streak_30' => $streak >= 30,
            'streak_100' => $streak >= 100,
            'reviews_10' => $reviews >= 10,
            'challenges_10' => $challenges >= 10,
            default => false,
        }));
    }

    private static function give(User $user, string $key, string $name, string $emoji, string $description): bool
    {
        $badge = UserBadge::firstOrCreate(['user_id' => $user->id, 'key' => $key], ['name' => $name, 'emoji' => $emoji, 'description' => $description]);
        if (! $badge->wasRecentlyCreated) {
            return false;
        }

        BookwormNotification::send($user, 'badge', "{$emoji} New badge: {$name}! {$description}.", route('challenges.index'));

        return true;
    }

    public static function badgeKey(CarbonInterface $month, string $key): string
    {
        return 'challenge:' . $month->format('Y-m') . ':' . $key;
    }

    /** Books finished in the month, with their books. */
    private static function finished(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $user->shelfItems()->with('book')->where('status', 'read')->whereBetween('read_at', [$from, $to])->get();
    }

    private static function pages($item): int
    {
        return (int) ($item->total_pages ?: $item->book?->page_count ?: 0);
    }

    private static function measure(string $key, User $user, CarbonInterface $from, CarbonInterface $to, Collection $finished): int
    {
        return match ($key) {
            'chunky' => $finished->filter(fn ($i) => self::pages($i) > 400)->count(),
            'short' => $finished->filter(fn ($i) => self::pages($i) > 0 && self::pages($i) < 200)->count(),
            'classic' => $finished->filter(fn ($i) => $i->book?->first_publish_year && $i->book->first_publish_year < 1950)->count(),
            'three', 'five' => $finished->count(),
            'new_author' => self::newAuthors($user, $from, $finished),
            'days' => ReadingSession::where('user_id', $user->id)->whereBetween('read_on', [$from->toDateString(), $to->toDateString()])->distinct()->count('read_on'),
            'pages' => (int) ReadingSession::where('user_id', $user->id)->whereBetween('read_on', [$from->toDateString(), $to->toDateString()])->sum('pages_read'),
            'reviews' => Review::where('user_id', $user->id)->whereNotNull('body')->where('body', '!=', '')->whereBetween('created_at', [$from, $to])->count(),
            'quote' => Post::where('user_id', $user->id)->where('type', 'quote')->whereBetween('created_at', [$from, $to])->count(),
            'together' => self::joinedTogether($user, $from, $to),
            default => 0,
        };
    }

    private static function newAuthors(User $user, CarbonInterface $from, Collection $finished): int
    {
        $before = $user->shelfItems()->where('status', 'read')->where('read_at', '<', $from)
            ->join('books', 'books.id', '=', 'user_book_shelves.book_id')
            ->pluck('books.author_name')->filter()->map(fn ($a) => mb_strtolower($a))->flip();

        return $finished->filter(fn ($i) => $i->book?->author_name && ! $before->has(mb_strtolower($i->book->author_name)))
            ->pluck('book.author_name')->unique()->count();
    }

    private static function joinedTogether(User $user, CarbonInterface $from, CarbonInterface $to): int
    {
        $count = DB::table('buddy_read_user')->where('user_id', $user->id)->whereBetween('joined_at', [$from, $to])->count();
        if (Schema::hasTable('book_club_members')) {
            $count += DB::table('book_club_members')->where('user_id', $user->id)->whereBetween('created_at', [$from, $to])->count();
        }

        return $count;
    }
}
