<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Acorns;
use Illuminate\Console\Command;

/** Give a reader acorns by hand (for testing the Tree shop, or as a gift). */
class GiveAcorns extends Command
{
    protected $signature = 'acorns:give {user : username, email or name} {amount=1000}';

    protected $description = 'Give a reader acorns to spend in the Tree shop';

    public function handle(): int
    {
        $search = trim($this->argument('user'));
        $amount = (int) $this->argument('amount');
        if ($amount <= 0) {
            $this->error('The amount must be more than 0.');

            return self::FAILURE;
        }

        $user = User::whereRaw('LOWER(username) = ?', [mb_strtolower($search)])
            ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($search)])
            ->first();

        if (! $user) {
            $matches = User::whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                ->orWhereRaw('LOWER(username) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                ->limit(10)->get();
            if ($matches->count() !== 1) {
                $this->error($matches->isEmpty() ? "No reader matches “{$search}”." : "More than one reader matches “{$search}” — use their username:");
                $matches->each(fn (User $u) => $this->line("  {$u->username}  ({$u->name})"));

                return self::FAILURE;
            }
            $user = $matches->first();
        }

        Acorns::award($user, $amount, 'gift', null, 'as a gift');
        $this->info("Gave {$amount} acorns to {$user->name} (@{$user->username}). They now have " . $user->fresh()->acorns . '.');

        return self::SUCCESS;
    }
}
