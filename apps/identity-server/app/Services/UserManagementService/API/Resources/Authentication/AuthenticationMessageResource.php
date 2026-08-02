<?php

namespace App\Services\UserManagementService\API\Resources\Authentication;

use Zolta\Http\Response\Resources\Resource;

final class AuthenticationMessageResource extends Resource
{
    public function toArray(): array
    {
        $response = ['message' => $this->get('message')];
        $developmentCode = $this->get('developmentCode');

        if (
            config('app.expose_email_verification_code')
            && is_string($developmentCode)
            && $developmentCode !== ''
        ) {
            $response['development_code'] = $developmentCode;
        }

        return $response;
    }
}
