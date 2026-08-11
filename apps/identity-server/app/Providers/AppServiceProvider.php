<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\MappedLaravelEventDispatcher;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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

        RateLimiter::for('identity-hosted-login', static function (Request $request): array {
            return [
                Limit::perMinute(20)->by(self::hostedThrottleKey($request, 'login:email', (string) $request->input('email'))),
                Limit::perMinute(120)->by(self::hostedThrottleKey($request, 'login:application', $request->ip() ?? 'unknown')),
            ];
        });

        RateLimiter::for('identity-hosted-register', static function (Request $request): array {
            return [
                Limit::perMinute(10)->by(self::hostedThrottleKey($request, 'register:email', (string) $request->input('email'))),
                Limit::perMinute(40)->by(self::hostedThrottleKey($request, 'register:application', $request->ip() ?? 'unknown')),
            ];
        });

        Gate::before(static function (User $user): ?bool {
            return $user->is_system_admin ? true : null;
        });
    }

    private static function hostedThrottleKey(Request $request, string $scope, string $subject): string
    {
        $application = Str::lower(trim((string) $request->route('application')));
        $normalizedSubject = Str::lower(trim($subject));

        return 'identity-hosted:'.$scope.':'.hash('sha256', $application.'|'.$normalizedSubject);
    }
}
