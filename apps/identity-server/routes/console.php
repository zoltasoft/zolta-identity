<?php

use App\Services\UserManagementService\Application\Contracts\TemporaryAccountManagerInterface;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(static function (): void {
    app(TemporaryAccountManagerInterface::class)->purgeExpired();
})->name('purge-expired-demo-accounts')->everyMinute()->withoutOverlapping();
