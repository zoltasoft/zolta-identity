<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class AccountDataExportResource extends Resource
{
    public function toArray(): array
    {
        return ['account_export' => $this->get('accountExport')];
    }
}
