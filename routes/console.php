<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('crm:generate-renewal-follow-ups')->dailyAt('06:00');
Schedule::command('crm:send-follow-up-digests')->dailyAt('07:00');
