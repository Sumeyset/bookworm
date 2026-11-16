<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/** Let a reader review reports (or take that away). */
class MakeAdmin extends Command
{
    protected $signature = 'bookworm:admin {user : username or email} {--remove : take admin away}';

    protected $description = 'Make a reader an admin who can review reports';

    public function handle(): int
    {
        $search = mb_strtolower($this->argument('user'));
        $user = User::whereRaw('LOWER(username) = ?', [$search])->orWhereRaw('LOWER(email) = ?', [$search])->first();
        if (! $user) {
            $this->error("No reader with username or email “{$search}”.");

            return self::FAILURE;
        }
        $user->forceFill(['is_admin' => ! $this->option('remove')])->save();
        $this->info($this->option('remove') ? "@{$user->username} is no longer an admin." : "@{$user->username} is now an admin. Reports: /admin/reports");

        return self::SUCCESS;
    }
}
