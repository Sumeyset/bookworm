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
        $limit = (int) $this->option('limit');
        $done = 0;
        $failed = 0;
        $colours = 0;

        // Every book with a cover; only missing covers count towards --limit,
        // so big libraries are finished over a few runs.
        Book::query()->whereNotNull('cover_url')->orderBy('id')->chunkById(500, function ($books) use ($limit, &$done, &$failed, &$colours) {
            foreach ($books as $book) {
                $cached = \Illuminate\Support\Facades\Storage::disk('local')->exists(CoverCache::relativePath($book));
                if (! $cached) {
                    if ($done + $failed >= $limit) {
                        return false;
                    }
                    CoverCache::ensure($book) ? $done++ : $failed++;
                }
                if (! $book->spine_color && \App\Support\SpineColor::for($book)) {
                    $colours++;
                }
            }
        });

        $this->info("Covers downloaded: {$done}. Could not fetch: {$failed}. Spine colours worked out: {$colours}.");

        return self::SUCCESS;
    }
}
