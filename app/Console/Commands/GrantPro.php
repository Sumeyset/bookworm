<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Give a reader Pro by hand (friends, testers, prizes). */
class GrantPro extends Command
{
    protected $signature = 'bookworm:pro {user : username or email} {days=30 : days of Pro (0 takes it away)}';

    protected $description = 'Give a reader Bookworm Pro for some days';

    public function handle(): int
    {
        $search = mb_strtolower($this->argument('user'));
        $user = User::whereRaw('LOWER(username) = ?', [$search])->orWhereRaw('LOWER(email) = ?', [$search])->first();
        if (! $user) {
            $this->error("No reader with username or email “{$search}”.");

            return self::FAILURE;
        }
        $days = (int) $this->argument('days');
        $from = $user->pro_until?->isFuture() ? $user->pro_until : now();
        $user->forceFill(['pro_until' => $days > 0 ? $from->copy()->addDays($days) : now()])->save();
        $this->info($days > 0 ? "@{$user->username} has Pro until {$user->pro_until->format('j M Y')}." : "@{$user->username} is no longer Pro.");

        return self::SUCCESS;
    }
}
