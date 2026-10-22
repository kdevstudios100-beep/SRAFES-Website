<?php

use Illuminate\Support\Facades\Schedule;

/*
| Requires one cron entry on the server (see docs/OPERATIONS.md):
| * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command('srafes:backup')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('srafes:backup:verify')->dailyAt('02:30');
