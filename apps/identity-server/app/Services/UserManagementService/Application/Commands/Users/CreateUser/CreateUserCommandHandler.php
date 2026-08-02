<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\CreateUser;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Factories\RoleFactory;
use App\Services\UserManagementService\Domain\Factories\UserFactory;
use App\Services\UserManagementService\Domain\Repositories\RoleRepository;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\RoleId;

#[HandlesCommand(CreateUserCommand::class)]
final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserFactory $userFactory,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private RoleFactory $roleFactory,
    ) {}

    public function __invoke(CreateUserCommand $createUserCommand): Result
    {
        $roleId = $createUserCommand->roleId instanceof RoleId
            ? $createUserCommand->roleId
            : new RoleId((string) $createUserCommand->roleId);

        $role = $this->roleRepository->findRoleById($roleId);
        if ($role === null) {
            $role = $this->roleFactory->getDefaultRole();
        }

        $user = $this->userFactory->create([
            'username' => $createUserCommand->username->get('username'),
            'email' => $createUserCommand->email->get('address'),
            'password' => $createUserCommand->password,
            'terms' => $createUserCommand->terms,
        ], $role);

        $this->userRepository->saveUser($user);

        return Result::success(new UserPayload($user));
    }
}
