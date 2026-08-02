<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\RegisterUser;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(RegisterUserCommand::class)]
class RegisterUserCommandValidator
{
    public function __construct(private readonly UserPolicy $userPolicy) {}

    public function __invoke(RegisterUserCommand $registerUserCommand): Result
    {
        $this->userPolicy->assertCanRegister($registerUserCommand->email);

        return Result::success();
    }
}
