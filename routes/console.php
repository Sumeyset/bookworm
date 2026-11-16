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

// Reading streaks: freezes cover yesterday just after midnight; reminders go out hourly
// (each reader gets theirs at the hour they picked, in their own time zone).
Schedule::call(fn () => \App\Support\Streak::applyFreezes())->name('streaks:freezes')->dailyAt('00:05');
Schedule::call(fn () => \App\Support\Streak::sendReminders())->name('streaks:reminders')->hourlyAt(2);

// Barnaby's pick of the week, Monday morning, for readers who read in the last two weeks.
Schedule::call(fn () => \App\Support\Barnaby\WeeklyPicks::send())->name('barnaby:weekly')->weeklyOn(1, '10:17');

// The weekly email, Sunday evening.
Schedule::call(fn () => \App\Support\Digest::sendAll())->name('email:weekly')->weeklyOn(0, '17:23');
