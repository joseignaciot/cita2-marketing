<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('gsc:sync-all')->dailyAt('03:00');
Schedule::command('reports:clean-expired-cache')->hourly();
Schedule::command('reports:clean')->weekly();
