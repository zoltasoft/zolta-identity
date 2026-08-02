<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\Commands\Authentication\ProvisionTemporaryAccount\ProvisionTemporaryAccountCommand;
use App\Services\UserManagementService\Application\DTOs\Output\TemporaryAccountResponseDTO;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ProvisionTemporaryAccountService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(): TemporaryAccountResponseDTO
    {
        ['credentials' => $credentials] = $this->applicationService
            ->runAndCapture(ProvisionTemporaryAccountCommand::class)
            ->getOrFail();

        return new TemporaryAccountResponseDTO(...$credentials);
    }
}
