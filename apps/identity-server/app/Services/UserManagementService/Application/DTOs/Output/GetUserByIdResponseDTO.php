<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use App\Services\UserManagementService\Domain\Aggregates\Permission;
use App\Services\UserManagementService\Domain\Aggregates\User;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class GetUserByIdResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly array $user,
    ) {}

    public static function fromDomain(User $user): self
    {
        return new self([
            'id' => $user->getId()->get(),
            'email' => $user->getEmail()->address,
            'username' => $user->getUsername()->username,
            'role' => $user->getRole() ? [
                'id' => $user->getRole()->getId()->get(),
                'name' => $user->getRole()->getName()->get(),
                'description' => $user->getRole()->getDescription()?->get(),
                'permissions' => array_map(
                    static fn (Permission $permission): array => [
                        'id' => $permission->getId()->get(),
                        'name' => $permission->getName()->get(),
                        'description' => $permission->getDescription()?->get(),
                    ],
                    $user->getRole()->getPermissions()
                ),
            ] : null,
            'permissions' => array_map(
                static fn (Permission $permission): array => [
                    'id' => $permission->getId()->get(),
                    'name' => $permission->getName()->get(),
                    'description' => $permission->getDescription()?->get(),
                ],
                $user->getPermissions()
            ),
            'created_at' => $user->getCreatedAt()->format('c'),
            'updated_at' => $user->getUpdatedAt()->format('c'),
        ]);
    }
}
