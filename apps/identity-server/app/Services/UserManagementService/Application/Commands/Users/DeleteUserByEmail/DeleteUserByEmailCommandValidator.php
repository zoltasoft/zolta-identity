<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserByEmail;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(DeleteUserByEmailCommand::class)]
final readonly class DeleteUserByEmailCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(DeleteUserByEmailCommand $deleteUserByEmailCommand): Result
    {
        $this->userPolicy->assertExistUser($deleteUserByEmailCommand->email);

        return Result::success();
    }
}
