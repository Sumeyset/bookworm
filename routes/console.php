<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily jobs. Run the scheduler on the server with a cron entry:
//   * * * * * cd /path/to/bookworm && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('backup:database')->dailyAt('03:10');
Schedule::command('covers:warm --limit=500')->dailyAt('04:20');
