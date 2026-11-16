<?php

namespace App\Support\Barnaby;

use App\Models\ReadingSession;
use App\Models\User;
use App\Notifications\BookwormNotification;

/** Monday morning: Barnaby's pick of the week, as a notification for readers who read lately. */
class WeeklyPicks
{
    public static function send(int $limit = 100): int
    {
        if (! BarnabyClient::configured()) {
            return 0;
        }

        $barnaby = app(Barnaby::class);
        $active = ReadingSession::where('read_on', '>=', today()->subDays(14))->distinct()->pluck('user_id');
        $sent = 0;

        User::whereIn('id', $active)->inRandomOrder()->take($limit)->get()->each(function (User $user) use ($barnaby, &$sent) {
            try {
                $book = $barnaby->weeklyPick($user);
            } catch (BarnabyUnavailable) {
                return false; // the AI is out of quota: try again next week
            }
            if (! $book) {
                return;
            }
            BookwormNotification::send($user, 'barnaby', "🪱 Barnaby’s pick for you this week: “{$book['title']}”" . ($book['author'] ? " by {$book['author']}" : '') . ". {$book['why']}", $book['url']);
            $sent++;
        });

        return $sent;
    }
}
