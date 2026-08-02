<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateUserEmail;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(UpdateUserEmailCommand::class)]
final readonly class UpdateUserEmailCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(UpdateUserEmailCommand $updateUserEmailCommand): Result
    {
        $this->userPolicy->assertCanUpdateEmail(email: $updateUserEmailCommand->email, userId: $updateUserEmailCommand->id);

        return Result::success();
    }
}
