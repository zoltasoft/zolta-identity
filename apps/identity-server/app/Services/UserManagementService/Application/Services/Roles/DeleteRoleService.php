<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Roles;

use App\Services\UserManagementService\Application\Commands\Roles\DeleteRole\DeleteRoleCommand;
use App\Services\UserManagementService\Application\DTOs\Input\DeleteRoleDTO;
use App\Services\UserManagementService\Application\DTOs\Output\DeleteRoleResponseDTO;
use App\Services\UserManagementService\Application\Queries\Roles\GetRoleById\GetRoleByIdQuery;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class DeleteRoleService
{
    public function __construct(
        private ApplicationService $applicationService,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(DeleteRoleDTO $deleteRoleDTO): DeleteRoleResponseDTO
    {
        $this->applicationService->capture(['input' => [
            'roleId' => $deleteRoleDTO->roleId,
        ]]);

        $roleData = $this->applicationService->runAndCapture(GetRoleByIdQuery::class, [
            'id' => $deleteRoleDTO->roleId,
        ])->getOrFail(fn (): RuntimeException => new RuntimeException('Role not found'));

        $role = $roleData['role'];

        if ($role->isSystemRole()) {
            throw new RuntimeException('System roles cannot be deleted');
        }

        $assignedUsers = $this->userRepository->countByRole($role->getId()->get('value'));
        if ($assignedUsers > 0) {
            throw new RuntimeException('Role is assigned to users and cannot be deleted');
        }

        $result = $this->applicationService->runAndCapture(DeleteRoleCommand::class, [
            'roleId' => $deleteRoleDTO->roleId,
        ]);

        $result->getOrFail(fn (): RuntimeException => new RuntimeException('Unable to delete role'));

        return new DeleteRoleResponseDTO(
            roleId: $deleteRoleDTO->roleId,
            message: 'Role deleted successfully.'
        );
    }
}
