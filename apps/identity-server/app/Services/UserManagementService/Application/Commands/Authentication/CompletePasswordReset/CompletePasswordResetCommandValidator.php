<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\CompletePasswordReset;

use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(CompletePasswordResetCommand::class)]
final readonly class CompletePasswordResetCommandValidator
{
    public function __invoke(CompletePasswordResetCommand $command): Result
    {
        return Result::success();
    }
}
