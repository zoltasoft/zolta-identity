<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

/**
 * Data Transfer Object representing the response of a successful login.
 * Contains the access token and optionally the authenticated user data.
 */
final class LoginResource extends Resource
{
    public function toArray(): array
    {
        // You can map what ever you want manually or get the entire returned data array
        return $this->all();
    }
}
