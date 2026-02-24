<?php

use App\Console\Commands\MarkNoShowReservations;
use App\Console\Commands\SoftDeleteExpiredTimeouts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SoftDeleteExpiredTimeouts::class)->everyMinute();

Schedule::command(MarkNoShowReservations::class)->everyMinute();
