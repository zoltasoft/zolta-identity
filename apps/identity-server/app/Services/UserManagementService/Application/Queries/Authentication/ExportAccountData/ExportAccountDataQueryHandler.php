<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Authentication\ExportAccountData;

use App\Services\UserManagementService\Application\Contracts\AccountDataExporterInterface;
use App\Services\UserManagementService\Application\Payloads\Authentication\AccountDataExportPayload;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ExportAccountDataQuery::class)]
final readonly class ExportAccountDataQueryHandler
{
    public function __construct(private AccountDataExporterInterface $exporter) {}

    public function __invoke(ExportAccountDataQuery $query): Option
    {
        return Option::some(new AccountDataExportPayload(
            $this->exporter->export($query->userId)
        ));
    }
}
