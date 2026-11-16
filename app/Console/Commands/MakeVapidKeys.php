<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

/** Make the key pair push notifications need. Put the output in .env. */
class MakeVapidKeys extends Command
{
    protected $signature = 'webpush:keys';

    protected $description = 'Make VAPID keys for push notifications (add them to .env)';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();
        $this->info('Add these lines to your .env file, then restart the server:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->comment('Keep the private key secret. If you change the keys, readers need to turn push on again.');

        return self::SUCCESS;
    }
}
