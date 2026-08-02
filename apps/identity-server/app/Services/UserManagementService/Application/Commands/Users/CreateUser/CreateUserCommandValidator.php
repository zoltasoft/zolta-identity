<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\CreateUser;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(CreateUserCommand::class)]
final readonly class CreateUserCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(CreateUserCommand $createUserCommand): Result
    {
        $this->userPolicy->assertExistUser($createUserCommand->email);

        return Result::success();
    }
}
