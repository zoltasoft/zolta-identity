<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\AttemptLogin;

use App\Services\UserManagementService\Application\Contracts\AuthenticationServiceInterface;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Exceptions\ValidationException;

#[HandlesCommand(AttemptLoginCommand::class)]
final readonly class AttemptLoginCommandHandler
{
    public function __construct(private AuthenticationServiceInterface $authenticationService) {}

    public function __invoke(AttemptLoginCommand $attemptLoginCommand): Result
    {
        if (! $this->authenticationService->attemptLogin($attemptLoginCommand->credential)) {
            throw new ValidationException(['credentials' => 'Invalid credentials provided.']);
        }

        return Result::success();
    }
}
