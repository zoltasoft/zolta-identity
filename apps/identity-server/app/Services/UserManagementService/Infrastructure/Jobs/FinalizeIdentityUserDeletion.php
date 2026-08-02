<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Jobs;

use App\Services\UserManagementService\Application\Contracts\AccountDataEraserInterface;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityWebhookDelivery;
use App\Services\UserManagementService\Infrastructure\Models\Eloquent\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Zolta\Domain\ValueObjects\UserId;

final class FinalizeIdentityUserDeletion implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly string $userId) {}

    public function handle(AccountDataEraserInterface $eraser): void
    {
        $pending = IdentityWebhookDelivery::query()
            ->where('event', 'identity.user.deletion_requested')
            ->where('subject_id', $this->userId)
            ->where('status', '!=', 'delivered')
            ->exists();
        if ($pending) {
            return;
        }

        $user = User::query()->find($this->userId);
        if (! $user) {
            return;
        }
        $eraser->erase(new UserId($this->userId), (string) $user->email);
        $user->delete();
    }
}
