<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\CoverCache;
use Illuminate\Console\Command;

class WarmCovers extends Command
{
    protected $signature = 'covers:warm {--limit=500 : Most books to process in one run}';

    protected $description = 'Download and store book covers locally so pages load faster';

    public function handle(): int
    {
        $done = 0;
        $failed = 0;

        Book::query()->whereNotNull('cover_url')->orderBy('id')->limit((int) $this->option('limit'))->each(function (Book $book) use (&$done, &$failed) {
            CoverCache::ensure($book) ? $done++ : $failed++;
        });

        $this->info("Covers stored: {$done}. Could not fetch: {$failed}.");

        return self::SUCCESS;
    }
}
