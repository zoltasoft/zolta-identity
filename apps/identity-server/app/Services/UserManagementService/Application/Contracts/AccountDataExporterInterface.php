<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

use Zolta\Domain\ValueObjects\UserId;

interface AccountDataExporterInterface
{
    public function export(UserId $userId): array;
}
