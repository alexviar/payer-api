<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --tries=1 --stop-when-empty')
    ->withoutOverlapping()
    ->everyTenSeconds()
    ->appendOutputTo(storage_path('logs/queue.log'));

Schedule::command('reports:clean-expired')
    ->everyThirtyMinutes()
    ->appendOutputTo(storage_path('logs/reports-cleanup.log'));
