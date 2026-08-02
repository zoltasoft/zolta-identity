<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Infrastructure\Services\Identity;

use App\Services\UserManagementService\Infrastructure\Models\Eloquent\IdentityProjectClient;
use Illuminate\Support\Str;

final class IdentityClientProvisioner
{
    /** @return array{0: IdentityProjectClient, 1: string} */
    public function create(string $projectId, string $name): array
    {
        $secret = Str::random(64);
        $client = IdentityProjectClient::query()->create([
            'project_id' => $projectId,
            'name' => $name,
            'secret_hash' => hash('sha256', $secret),
            'secret_prefix' => Str::substr($secret, 0, 8),
            'status' => 'active',
        ]);

        return [$client, $secret];
    }
}
