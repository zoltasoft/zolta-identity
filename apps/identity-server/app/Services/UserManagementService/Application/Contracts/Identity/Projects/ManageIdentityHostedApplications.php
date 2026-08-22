<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts\Identity\Projects;

use App\Services\UserManagementService\Application\DTOs\External\UploadedAsset;

interface ManageIdentityHostedApplications
{
    /** @param array{name: string, key: string, primary_client_id: string, sandbox_client_id?: string|null, application_url: string, callback_url: string} $attributes @return array<string, mixed> */
    public function createHostedApplication(string $actorUserId, string $projectId, array $attributes): array;

    /** @param array{name: string, primary_client_id: string, sandbox_client_id?: string|null, application_url: string, callback_url: string, status: string} $attributes */
    public function updateHostedApplication(string $actorUserId, string $projectId, string $applicationId, array $attributes): void;

    public function removeHostedApplication(string $actorUserId, string $projectId, string $applicationId): void;

    /** @return array<string, mixed> */
    public function uploadHostedApplicationLogo(string $actorUserId, string $projectId, string $applicationId, UploadedAsset $logo): array;

    public function removeHostedApplicationLogo(string $actorUserId, string $projectId, string $applicationId): void;
}
