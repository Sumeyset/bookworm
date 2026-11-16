<?php

namespace App\Support;

use App\Mail\WeeklyDigest;
use App\Models\ReadingSession;
use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** The weekly email: built per reader, sent only when there's something to say. */
class Digest
{
    public static function for(User $user): ?array
    {
        $since = now()->subWeek();
        $friendIds = $user->following()->pluck('users.id');

        $friendsFinished = UserBookShelf::with(['user', 'book'])
            ->whereIn('user_id', $friendIds)->where('status', 'read')->where('read_at', '>=', $since)
            ->latest('read_at')->take(8)->get()
            ->filter(fn ($item) => $item->user && $item->book && ! $item->user->hide_activity && $item->user->visibleTo($user))
            ->map(fn ($item) => ['name' => $item->user->name, 'title' => $item->book->title, 'url' => route('books.show', $item->book)])
            ->values()->all();

        $sessions = ReadingSession::where('user_id', $user->id)->where('read_on', '>=', $since->toDateString())->get();
        $mine = [
            'days' => $sessions->pluck('read_on')->map(fn ($d) => (string) $d)->unique()->count(),
            'pages' => (int) $sessions->sum('pages_read'),
            'minutes' => (int) $sessions->sum('minutes'),
            'finished' => $user->shelfItems()->with('book')->where('status', 'read')->where('read_at', '>=', $since)->get()->pluck('book.title')->filter()->values()->all(),
            'streak' => Streak::current($user),
        ];
        $challenges = Challenges::progress($user)->map(fn ($c) => ['name' => $c['name'], 'emoji' => $c['emoji'], 'done' => $c['done'], 'progress' => $c['progress'], 'goal' => $c['goal']])->all();

        if (! $friendsFinished && ! $mine['days'] && ! $mine['finished']) {
            return null; // a quiet week: no email
        }

        return ['friends' => $friendsFinished, 'mine' => $mine, 'challenges' => $challenges];
    }

    /** Sunday evening job. */
    public static function sendAll(): int
    {
        $sent = 0;
        User::where('weekly_email', true)->whereNull('suspended_at')
            ->where(fn ($q) => $q->whereNull('last_digest_at')->orWhere('last_digest_at', '<', now()->subDays(6)))
            ->whereHas('readingSessions', fn ($q) => $q->where('read_on', '>=', now()->subDays(60)->toDateString()))
            ->chunkById(200, function ($users) use (&$sent) {
                foreach ($users as $user) {
                    $digest = self::for($user);
                    if (! $digest) {
                        continue;
                    }
                    try {
                        Mail::to($user)->send(new WeeklyDigest($user, $digest));
                        $user->forceFill(['last_digest_at' => now()])->save();
                        $sent++;
                    } catch (\Throwable $e) {
                        Log::warning('Weekly digest failed for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            });

        return $sent;
    }
}
