<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:expire', function () {
    $count = app(\App\Services\SubscriptionService::class)->expire();
    $this->info("{$count} langganan DOKU berakhir. Data toko tetap tersimpan.");
})->purpose('Expire paid Premium terms into Free without disabling businesses');

\Illuminate\Support\Facades\Schedule::command('subscriptions:expire')->everyMinute()->withoutOverlapping();
