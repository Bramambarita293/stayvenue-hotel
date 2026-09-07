<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('midtrans:check-pending')->everyThirtyMinutes();

// Sapu reservasi yang masa inap/acaranya lewat kemarin (01:00 WIB, trafik sepi).
Schedule::command('stays:auto-complete')->dailyAt('01:00');