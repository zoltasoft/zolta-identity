<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Permissions\CreatePermission;

use App\Services\UserManagementService\Domain\Repositories\PermissionRepository;
use RuntimeException;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(CreatePermissionCommand::class)]
final readonly class CreatePermissionCommandValidator
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    public function __invoke(CreatePermissionCommand $createPermissionCommand): Result
    {
        $existing = $this->permissionRepository->findPermissionByName($createPermissionCommand->name);
        if ($existing !== null) {
            return Result::failure(new RuntimeException('Permission name already exists'));
        }

        return Result::success();
    }
}
