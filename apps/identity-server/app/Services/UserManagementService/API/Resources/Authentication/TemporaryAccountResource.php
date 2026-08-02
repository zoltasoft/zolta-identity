<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class TemporaryAccountResource extends Resource
{
    public function toArray(): array
    {
        return [
            'email' => $this->get('email'),
            'password' => $this->get('password'),
            'expires_at' => $this->get('expires_at'),
        ];
    }
}
