<?php

use App\Console\Commands\ProcessSponsorshipExpiry;
use App\Console\Commands\SendSponsorshipReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ProcessSponsorshipExpiry::class)
    ->dailyAt('00:05')
    ->withoutOverlapping();

Schedule::command(SendSponsorshipReminders::class)
    ->dailyAt('09:00')
    ->withoutOverlapping();
