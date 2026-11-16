<?php

namespace App\Support;

use App\Models\ReadingSession;
use App\Models\User;
use App\Notifications\BookwormNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reading streaks: days in a row with reading logged. A streak survives until
 * the end of today. Streak freezes (bought in the Tree shop) fill in a missed
 * day automatically, and a reminder can nudge readers before a streak ends.
 */
class Streak
{
    public const FREEZE_PRICE = 120;

    public const MAX_FREEZES = 2;

    /** Reading days (and frozen days) in the last 400 days, newest first, as Y-m-d. */
    public static function days(User $user): Collection
    {
        $since = today()->subDays(400)->toDateString();
        $read = ReadingSession::where('user_id', $user->id)->where('read_on', '>=', $since)->pluck('read_on');
        $frozen = DB::table('streak_freezes')->where('user_id', $user->id)->where('day', '>=', $since)->pluck('day');

        return $read->merge($frozen)
            ->map(fn ($day) => Carbon::parse($day)->toDateString())
            ->unique()
            ->sortDesc()
            ->values();
    }

    public static function current(User $user): int
    {
        $days = self::days($user);
        $cursor = today();
        if ($days->first() !== $cursor->toDateString()) {
            $cursor = $cursor->subDay(); // still alive until the end of today
        }

        $streak = 0;
        foreach ($days as $day) {
            if ($day !== $cursor->toDateString()) {
                break;
            }
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /** The streak is alive but nothing is logged today yet: it ends at midnight. */
    public static function atRisk(User $user): bool
    {
        $days = self::days($user);

        return ! $days->contains(today()->toDateString()) && $days->contains(today()->subDay()->toDateString());
    }

    public static function maxFor(User $user): int
    {
        return $user->isPro() ? Pro::FREEZES : self::MAX_FREEZES;
    }

    /** Buy a freeze. Returns an error message, or null when bought. */
    public static function buyFreeze(User $user): ?string
    {
        return DB::transaction(function () use ($user) {
            $row = DB::table('users')->where('id', $user->id)->lockForUpdate()->first(['acorns', 'streak_freezes']);
            $max = self::maxFor($user);
            if ($row->streak_freezes >= $max) {
                return 'You can hold ' . $max . ' streak freezes at a time.';
            }
            if ($row->acorns < self::FREEZE_PRICE) {
                return 'You need ' . (self::FREEZE_PRICE - $row->acorns) . ' more acorns for a streak freeze.';
            }
            DB::table('users')->where('id', $user->id)->update([
                'acorns' => $row->acorns - self::FREEZE_PRICE,
                'streak_freezes' => $row->streak_freezes + 1,
            ]);
            $user->acornEvents()->create(['amount' => -self::FREEZE_PRICE, 'reason' => 'shop', 'note' => 'bought a streak freeze']);
            $user->acorns = $row->acorns - self::FREEZE_PRICE;
            $user->streak_freezes = $row->streak_freezes + 1;

            return null;
        });
    }

    /**
     * Run just after midnight: anyone with a freeze who missed yesterday, while
     * their streak was still going the day before, keeps the streak.
     */
    public static function applyFreezes(): int
    {
        $yesterday = today()->subDay()->toDateString();
        $dayBefore = today()->subDays(2)->toDateString();
        $saved = 0;

        User::where('streak_freezes', '>', 0)->each(function (User $user) use ($yesterday, $dayBefore, &$saved) {
            $days = self::days($user);
            if ($days->contains($yesterday) || ! $days->contains($dayBefore)) {
                return;
            }
            $inserted = DB::table('streak_freezes')->insertOrIgnore(['user_id' => $user->id, 'day' => $yesterday, 'created_at' => now(), 'updated_at' => now()]);
            if (! $inserted) {
                return;
            }
            $user->decrement('streak_freezes');
            $streak = self::current($user);
            BookwormNotification::send($user, 'streak', "🧊 A streak freeze covered yesterday — your {$streak}-day streak is safe. Read today to keep it going!", route('users.show', $user->username));
            $saved++;
        });

        return $saved;
    }

    /** Run every hour: nudge readers who asked, at their chosen hour, when their streak would end tonight. */
    public static function sendReminders(): int
    {
        $sent = 0;

        User::where('streak_reminder', true)->each(function (User $user) use (&$sent) {
            $local = now()->setTimezone($user->timezone ?: config('app.timezone'));
            if ((int) $local->format('G') !== (int) $user->reminder_hour) {
                return;
            }
            if ($user->last_reminded_on && Carbon::parse($user->last_reminded_on)->toDateString() === $local->toDateString()) {
                return;
            }
            if (! self::atRisk($user)) {
                return;
            }
            $streak = self::current($user);
            $freeze = $user->streak_freezes > 0 ? ' (A streak freeze will cover tonight if you can’t.)' : '';
            BookwormNotification::send($user, 'streak', "🔥 Your {$streak}-day reading streak ends tonight — read a few pages to keep it going!{$freeze}", route('library.index'));
            $user->forceFill(['last_reminded_on' => $local->toDateString()])->save();
            $sent++;
        });

        return $sent;
    }
}
