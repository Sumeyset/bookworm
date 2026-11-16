<?php

namespace App\Support;

use App\Models\AcornEvent;
use App\Models\ReadingSession;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBookShelf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Acorns: earned by reading, spent in the Tree shop.
 *
 *  Finish a book ......... 50 + 1 per 20 pages (max +50); up to 3 books a day
 *  Read pages ............ 1 per 10 pages moved forward, max 30 a day
 *  A reading day ......... 5, plus 25 for every 7-day streak
 *  Write a review ........ 15, once per book
 *  Yearly goal ........... 100 at halfway, 250 when reached
 *
 * Every reward is written down with a key, so nothing pays twice.
 * Imports and back-fills are quiet: old books don't pay.
 */
class Acorns
{
    public static bool $quiet = false;

    public const FINISH_BASE = 50;

    public const FINISH_PAGE_BONUS_MAX = 50;

    public const FINISHES_PER_DAY = 3;

    public const PAGES_PER_ACORN = 10;

    public const PAGES_DAILY_MAX = 30;

    public const READING_DAY = 5;

    public const STREAK_BONUS = 25;

    public const REVIEW = 15;

    public const WELCOME = 100;

    /**
     * The Tree shop. key => [name, emoji, price, description, on sale (null = always,
     * or [from MMDD, to MMDD], wrapping over New Year)].
     */
    public const ITEMS = [
        'lights' => ['Fairy lights', '💡', 150, 'Warm twinkling lights along every branch shelf.', null],
        'butterflies' => ['Butterflies', '🦋', 180, 'A few butterflies fluttering around the leaves.', null],
        'lanterns' => ['Lanterns', '🏮', 200, 'Little glowing lanterns hanging from the branches.', null],
        'nest' => ['Bird & nest', '🐦', 250, 'A robin in its nest on a branch.', null],
        'swing' => ['Rope swing', '🪢', 300, 'A wooden swing hanging from a low branch.', null],
        'squirrel' => ['Squirrel', '🐿️', 350, 'A curious squirrel climbing the trunk.', null],
        'owl' => ['Owl', '🦉', 400, 'A wise old owl keeping watch. It blinks.', null],
        'plant' => ['Potted plant', '🪴', 120, 'A little trailing plant for your shelf.', null],
        'candle' => ['Candle', '🕯️', 90, 'A flickering candle in a brass holder.', null],
        'bookends' => ['Brass bookends', '📚', 150, 'Bookends with three tiny books between them.', null],
        'cat' => ['Sleeping cat', '🐈', 300, 'A ginger cat curled up asleep. Its tail twitches.', null],
        'evening' => ['Starry evening', '🌙', 300, 'Evening light, a moon in the window and fireflies.', null],
        'autumn' => ['Autumn colours', '🍂', 250, 'Golden leaves, drifting leaves and pumpkins.', ['0901', '1130']],
        'snow' => ['Christmas set', '❄️', 400, 'Snow on the branches, baubles, a star, presents and falling snow.', ['1115', '0106']],
    ];

    /* ---------------- earning ---------------- */

    /** Give acorns once per key. Returns how many were given. */
    public static function award(User $user, int $amount, string $reason, ?string $key = null, ?string $note = null): int
    {
        if (self::$quiet || $amount <= 0) {
            return 0;
        }

        $given = DB::transaction(function () use ($user, $amount, $reason, $key, $note) {
            if ($key !== null && AcornEvent::where('user_id', $user->id)->where('key', $key)->exists()) {
                return 0;
            }
            AcornEvent::create(['user_id' => $user->id, 'amount' => $amount, 'reason' => $reason, 'key' => $key, 'note' => $note]);
            User::whereKey($user->id)->increment('acorns', $amount);

            return $amount;
        });

        if ($given > 0) {
            $user->acorns = (int) $user->acorns + $given;
            self::tell("🌰 +{$given} acorns" . ($note ? ' ' . $note : ''));
        }

        return $given;
    }

    /** A book was marked read. */
    public static function finishedBook(UserBookShelf $item): void
    {
        $user = $item->user;
        if (! $user || ! $item->book) {
            return;
        }
        $finishedToday = AcornEvent::where('user_id', $user->id)->where('reason', 'finish')->whereDate('created_at', today())->count();
        if ($finishedToday >= self::FINISHES_PER_DAY) {
            return;
        }
        $bonus = min(self::FINISH_PAGE_BONUS_MAX, intdiv((int) $item->total_pages, 20));
        self::award($user, self::FINISH_BASE + $bonus, 'finish', 'finish:' . $item->book_id, 'for finishing “' . $item->book->title . '”');
    }

    /** Any reading today: the day, a streak, and the pages read. */
    public static function readingActivity(User $user): void
    {
        if (self::$quiet) {
            return;
        }
        $today = today()->toDateString();
        self::award($user, self::READING_DAY, 'day', 'day:' . $today, 'for reading today');

        $streak = self::streak($user);
        if ($streak > 0 && $streak % 7 === 0) {
            self::award($user, self::STREAK_BONUS, 'streak', 'streak:' . $today, "for a {$streak}-day reading streak");
        }

        // A minute of listening counts like a page.
        $pages = (int) ReadingSession::where('user_id', $user->id)->whereDate('read_on', $today)->selectRaw('COALESCE(SUM(pages_read), 0) + COALESCE(SUM(minutes), 0) as total')->value('total');
        $due = min(self::PAGES_DAILY_MAX, intdiv($pages, self::PAGES_PER_ACORN));
        $earned = (int) AcornEvent::where('user_id', $user->id)->where('reason', 'pages')->whereDate('created_at', $today)->sum('amount');
        if ($due > $earned) {
            self::award($user, $due - $earned, 'pages', null, 'for the pages you read');
        }
    }

    public static function reviewed(Review $review): void
    {
        if ($review->user && filled($review->body)) {
            self::award($review->user, self::REVIEW, 'review', 'review:' . $review->book_id, 'for your review');
        }
    }

    public static function goal(User $user, int $percent, int $year): void
    {
        match ($percent) {
            50 => self::award($user, 100, 'goal', "goal50:$year", 'for reaching halfway to your goal'),
            100 => self::award($user, 250, 'goal', "goal100:$year", 'for reaching your reading goal!'),
            default => null,
        };
    }

    public static function welcome(User $user): void
    {
        self::award($user, self::WELCOME, 'welcome', 'welcome', 'as a welcome gift — visit the Tree shop!');
    }

    /** Days in a row (ending today or yesterday) with reading logged. */
    public static function streak(User $user): int
    {
        return Streak::current($user);
    }

    /* ---------------- the shop ---------------- */

    public static function onSale(string $item, ?CarbonInterface $date = null): bool
    {
        $window = self::ITEMS[$item][4] ?? null;
        if ($window === null) {
            return true;
        }
        $today = ($date ?? now())->format('md');
        [$from, $to] = $window;

        return $from <= $to ? ($today >= $from && $today <= $to) : ($today >= $from || $today <= $to);
    }

    /** Buy a decoration. Returns an error message, or null when bought. */
    public static function buy(User $user, string $item): ?string
    {
        if (! array_key_exists($item, self::ITEMS)) {
            return 'That decoration doesn’t exist.';
        }
        [$name, , $price] = self::ITEMS[$item];
        if ($user->decorations()->where('item', $item)->exists()) {
            return "You already have the {$name}.";
        }
        if (! self::onSale($item)) {
            return "The {$name} aren’t in the shop right now — they come back in season.";
        }

        return DB::transaction(function () use ($user, $item, $name, $price) {
            // Re-read the balance inside the transaction so it can't go below zero.
            $balance = (int) User::whereKey($user->id)->lockForUpdate()->value('acorns');
            if ($balance < $price) {
                return 'You need ' . ($price - $balance) . ' more acorns for the ' . $name . '. Keep reading! 📚';
            }
            User::whereKey($user->id)->decrement('acorns', $price);
            AcornEvent::create(['user_id' => $user->id, 'amount' => -$price, 'reason' => 'shop', 'key' => 'shop:' . $item, 'note' => 'bought ' . $name]);
            $user->decorations()->create(['item' => $item, 'enabled' => true]);
            $user->acorns = $balance - $price;

            return null;
        });
    }

    /** Buy a decoration for another reader. Returns an error message, or null when sent. */
    public static function gift(User $giver, User $friend, string $item): ?string
    {
        if (! array_key_exists($item, self::ITEMS)) {
            return 'That decoration doesn’t exist.';
        }
        [$name, $emoji, $price] = self::ITEMS[$item];
        if ($giver->is($friend)) {
            return 'Gifts are for friends — buy it for yourself above!';
        }
        if ($friend->decorations()->where('item', $item)->exists()) {
            return "{$friend->name} already has the {$name}.";
        }
        if (! self::onSale($item)) {
            return "The {$name} aren’t in the shop right now — they come back in season.";
        }

        $error = DB::transaction(function () use ($giver, $friend, $item, $name, $price) {
            $balance = (int) User::whereKey($giver->id)->lockForUpdate()->value('acorns');
            if ($balance < $price) {
                return 'You need ' . ($price - $balance) . ' more acorns to gift the ' . $name . '.';
            }
            User::whereKey($giver->id)->decrement('acorns', $price);
            AcornEvent::create(['user_id' => $giver->id, 'amount' => -$price, 'reason' => 'gift', 'note' => 'gave ' . $friend->name . ' the ' . $name]);
            $friend->decorations()->create(['item' => $item, 'enabled' => true, 'gifted_by' => $giver->id]);
            $giver->acorns = $balance - $price;

            return null;
        });

        if ($error === null) {
            \App\Notifications\BookwormNotification::send($friend, 'gift', "{$giver->name} gave you the {$emoji} {$name} for your shelf! It’s already up.", route('users.show', $friend->username), $giver);
        }

        return $error;
    }

    /** @return list<string> decoration keys switched on for this reader */
    public static function enabled(User $user): array
    {
        return $user->decorations()->where('enabled', true)->pluck('item')
            ->filter(fn ($item) => array_key_exists($item, self::ITEMS))
            ->values()
            ->all();
    }

    private static function tell(string $message): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }
        try {
            session()->push('acorn_toasts', $message);
        } catch (\Throwable) {
            // no session (queue, console): nothing to show
        }
    }
}
