<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services;

use App\Services\UserManagementService\Application\Contracts\PasswordRecoveryServiceInterface;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;
use Zolta\Exceptions\ValidationException;

final class LaravelPasswordRecoveryService implements PasswordRecoveryServiceInterface
{
    public function requestResetLink(string $email): void
    {
        $status = Password::broker()->sendResetLink(['email' => $email]);

        // Do not reveal whether the submitted email belongs to an account.
        if (in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return;
        }

        if ($status === Password::RESET_THROTTLED) {
            throw new ValidationException([
                'email' => ['Please wait before requesting another reset link.'],
            ]);
        }

        throw new RuntimeException('Unable to send the password reset link.');
    }

    public function resetPassword(string $email, string $token, string $password): string
    {
        $userId = null;
        $status = Password::broker()->reset(
            [
                'email' => $email,
                'token' => $token,
                'password' => $password,
                'password_confirmation' => $password,
            ],
            static function (User $user, string $password) use (&$userId): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                $user->tokens()->delete();
                $userId = (string) $user->getKey();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new ValidationException([
                'token' => ['This password reset link is invalid or has expired.'],
            ]);
        }

        if ($userId === null) {
            throw new RuntimeException('Unable to resolve the password reset account.');
        }

        return $userId;
    }
}
