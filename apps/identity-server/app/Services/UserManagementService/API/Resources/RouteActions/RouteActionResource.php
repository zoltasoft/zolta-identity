<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Resources\RouteActions;

use Zolta\Http\Response\Resources\Resource;

final class RouteActionResource extends Resource
{
    public function toArray(): array
    {
        return $this->get('data');
    }
}
