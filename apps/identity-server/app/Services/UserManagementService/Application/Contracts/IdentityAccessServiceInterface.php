<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Contracts;

interface IdentityAccessServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function listInstallationUsers(string $actorUserId): array;

    public function updateInstallationUser(string $actorUserId, string $userId, bool $isSystemAdmin, bool $locked): void;

    /** @param array<string, mixed> $credentials @return array<string, mixed> */
    public function login(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function register(array $attributes, ?string $ipAddress = null, ?string $userAgent = null): array;

    /** @return array<string, mixed> */
    public function resendEmailVerification(string $userId): array;

    public function verifyEmail(string $userId, string $code): void;

    /** @return array<string, mixed> */
    public function requestPasswordReset(string $clientId, string $clientSecret, string $email): array;

    public function resetPassword(string $clientId, string $clientSecret, string $email, string $token, string $password): void;

    /** @param array<string, mixed> $credentials @return array<string, mixed> */
    public function refresh(array $credentials, ?string $ipAddress = null, ?string $userAgent = null): array;

    /** @return array<string, mixed> */
    public function introspect(string $clientId, string $clientSecret, string $accessToken): array;

    public function logout(string $accessToken): void;

    /** @return array<string, mixed> */
    public function currentIdentity(string $userId, string $accessToken): array;

    /** @return list<array<string, mixed>> */
    public function listSessions(string $userId, string $accessToken): array;

    public function revokeSession(string $userId, string $familyId): void;

    /** @return list<array<string, mixed>> */
    public function listProjects(string $actorUserId): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createProject(string $actorUserId, array $attributes): array;

    public function updateProjectRegistration(string $actorUserId, string $projectId, string $mode, ?string $roleId): void;

    /** @return array<string, mixed> */
    public function projectDetails(string $actorUserId, string $projectId): array;

    /** @return array<string, mixed> */
    public function createClient(string $actorUserId, string $projectId, string $name): array;

    /** @return array<string, mixed> */
    public function rotateClientSecret(string $actorUserId, string $projectId, string $clientId): array;

    public function setClientStatus(string $actorUserId, string $projectId, string $clientId, string $status): void;

    /** @param list<array{key: string, name?: string, description?: string}> $manifest @return list<array<string, mixed>> */
    public function syncPermissionManifest(string $actorUserId, string $projectId, string $clientId, array $manifest): array;

    /** @param list<array{key: string, name?: string, description?: string}> $manifest @return list<array<string, mixed>> */
    public function syncOwnPermissionManifest(string $clientId, string $clientSecret, array $manifest): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createRole(string $actorUserId, string $projectId, array $attributes): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createPermission(string $actorUserId, string $projectId, array $attributes): array;

    /** @param list<string> $permissionIds */
    public function setRolePermissions(string $actorUserId, string $projectId, string $roleId, array $permissionIds): void;

    /** @return array<string, mixed> */
    public function invite(string $actorUserId, string $projectId, string $email, bool $isAdmin): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function acceptInvitation(array $attributes): array;

    /** @param list<string> $roleIds @param list<string> $permissionIds */
    public function setMembershipAccess(
        string $actorUserId,
        string $projectId,
        string $membershipId,
        array $roleIds,
        array $permissionIds,
        bool $isAdmin,
        string $status,
    ): void;

    public function removeMembership(string $actorUserId, string $projectId, string $membershipId): void;

    /** @return list<array<string, mixed>> */
    public function listAuditEvents(string $actorUserId, string $projectId, int $limit = 100): array;
}
