<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\DeleteUserById;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(DeleteUserByIdCommand::class)]
final readonly class DeleteUserByIdCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(DeleteUserByIdCommand $deleteUserByIdCommand): Result
    {
        $this->userPolicy->assertExistUser($deleteUserByIdCommand->id);

        return Result::success();
    }
}
