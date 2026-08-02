<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SendWelcomeMessage;

use App\Services\UserManagementService\Application\Policies\UserPolicy;
use Zolta\Cqrs\Attributes\ValidatesCommand;
use Zolta\Cqrs\Services\Result;

#[ValidatesCommand(SendWelcomeMessageCommand::class)]
final readonly class SendWelcomeMessageCommandValidator
{
    public function __construct(private UserPolicy $userPolicy) {}

    public function __invoke(SendWelcomeMessageCommand $sendWelcomeMessageCommand): Result
    {
        $this->userPolicy->assertExistUser($sendWelcomeMessageCommand->id);

        return Result::success();
    }
}
