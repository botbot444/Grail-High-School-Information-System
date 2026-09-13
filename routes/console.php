<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Requires a single cron entry on the server:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Phase 7 — overdue fee detection. Runs before the school day so parents see an
// accurate banner when they log in; withoutOverlapping guards a slow run on a
// large fee table from stacking.
Schedule::command('fees:flag-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping();
