<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Users\UpdateAccountProfile;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(UpdateAccountProfileCommand::class)]
final readonly class UpdateAccountProfileCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(UpdateAccountProfileCommand $updateAccountProfileCommand): Result
    {
        $this->userPolicy->assertCanUpdateEmail(email: $updateAccountProfileCommand->email, userId: $updateAccountProfileCommand->userId);

        return Result::success();
    }
}
