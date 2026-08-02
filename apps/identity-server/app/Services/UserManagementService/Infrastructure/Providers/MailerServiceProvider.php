<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Application\Contracts\MailerService;
use App\Services\UserManagementService\Infrastructure\Services\LaravelMailerService;
use Illuminate\Support\ServiceProvider;

class MailerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MailerService::class, LaravelMailerService::class);
    }
}
