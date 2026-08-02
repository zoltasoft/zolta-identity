<?php

namespace App\Services\UserManagementService\Infrastructure\Providers;

use App\Services\UserManagementService\Infrastructure\Events\UserRegisteredEvent;
use App\Services\UserManagementService\Infrastructure\Listeners\SendWelcomeEmailListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ListenerServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(
            UserRegisteredEvent::class,
            [SendWelcomeEmailListener::class, 'handle']
        );
    }
}
