<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Authentication;

use App\Services\UserManagementService\Application\DTOs\Input\ExportAccountDataDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AccountDataExportResponseDTO;
use App\Services\UserManagementService\Application\Queries\Authentication\ExportAccountData\ExportAccountDataQuery;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class ExportAccountDataService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(ExportAccountDataDTO $dto): AccountDataExportResponseDTO
    {
        ['accountExport' => $export] = $this->applicationService
            ->runAndCapture(ExportAccountDataQuery::class, [
                'userId' => new UserId($dto->userId),
            ])
            ->getOrFail();

        return new AccountDataExportResponseDTO($export);
    }
}
