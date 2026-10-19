<?php

use Illuminate\Support\Facades\Schedule;

// Runs on `php artisan schedule:work` (the scheduler service in docker-compose),
// or on a server from one cron entry: * * * * * php artisan schedule:run
Schedule::command('tasks:remind')->hourly()->withoutOverlapping();
