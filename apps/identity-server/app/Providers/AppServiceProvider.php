<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\MappedLaravelEventDispatcher;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Zolta\Cqrs\Events\Contracts\EventDispatcherInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            EventDispatcherInterface::class,
            static function (Application $application): EventDispatcherInterface {
                $logger = $application->bound(LoggerInterface::class)
                    ? $application->make(LoggerInterface::class)
                    : null;

                return new MappedLaravelEventDispatcher(
                    laravelDispatcher: $application->make('events'),
                    eventMap: $application->make('event.map'),
                    logger: $logger,
                );
            }
        );
    }

    public function boot(): void
    {
        class_exists(User::class);
        Gate::before(static function (User $user): ?bool {
            return $user->is_system_admin ? true : null;
        });
    }
}
