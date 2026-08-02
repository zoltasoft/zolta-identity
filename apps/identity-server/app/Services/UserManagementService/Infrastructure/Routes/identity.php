<?php

use App\Services\UserManagementService\API\Controllers\Identity\IdentityAuthenticationController;
use App\Services\UserManagementService\API\Controllers\Identity\IdentityInstallationController;
use App\Services\UserManagementService\API\Controllers\Identity\IdentityProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/identity')->middleware('api')->group(function (): void {
    Route::post('auth/context', [IdentityAuthenticationController::class, 'context'])->middleware('throttle:120,1');
    Route::post('auth/login', [IdentityAuthenticationController::class, 'login'])->middleware('throttle:20,1');
    Route::post('auth/register', [IdentityAuthenticationController::class, 'register'])->middleware('throttle:10,1');
    Route::post('auth/sandbox-session', [IdentityAuthenticationController::class, 'sandboxSession'])->middleware('throttle:20,1');
    Route::post('auth/refresh', [IdentityAuthenticationController::class, 'refresh'])->middleware('throttle:60,1');
    Route::post('auth/password/forgot', [IdentityAuthenticationController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('auth/password/reset', [IdentityAuthenticationController::class, 'resetPassword'])->middleware('throttle:10,1');
    Route::post('auth/introspect', [IdentityAuthenticationController::class, 'introspect'])->middleware('throttle:300,1');
    Route::put('clients/permission-manifest', [IdentityAuthenticationController::class, 'syncPermissionManifest'])->middleware('throttle:60,1');
    Route::post('invitations/accept', [IdentityAuthenticationController::class, 'acceptInvitation'])->middleware('throttle:20,1');

    Route::middleware(['auth:sanctum', 'identity.token'])->group(function (): void {
        Route::get('auth/me', [IdentityAuthenticationController::class, 'me']);
        Route::get('auth/sessions', [IdentityAuthenticationController::class, 'sessions']);
        Route::delete('auth/sessions/{session}', [IdentityAuthenticationController::class, 'revokeSession']);
        Route::post('auth/logout', [IdentityAuthenticationController::class, 'logout']);
        Route::post('auth/email/verification/resend', [IdentityAuthenticationController::class, 'resendEmailVerification'])->middleware('throttle:5,1');
        Route::post('auth/email/verification', [IdentityAuthenticationController::class, 'verifyEmail'])->middleware('throttle:10,1');

        Route::get('users', [IdentityInstallationController::class, 'users']);
        Route::patch('users/{user}', [IdentityInstallationController::class, 'updateUser']);

        Route::get('projects', [IdentityProjectController::class, 'index']);
        Route::post('projects', [IdentityProjectController::class, 'store']);
        Route::get('projects/{project}', [IdentityProjectController::class, 'show']);
        Route::patch('projects/{project}/registration', [IdentityProjectController::class, 'updateRegistration']);
        Route::patch('projects/{project}/environment', [IdentityProjectController::class, 'updateEnvironment']);
        Route::post('projects/{project}/webhooks', [IdentityProjectController::class, 'storeWebhook']);
        Route::put('projects/{project}/webhooks/{webhook}', [IdentityProjectController::class, 'updateWebhook']);
        Route::post('projects/{project}/webhooks/{webhook}/rotate-secret', [IdentityProjectController::class, 'rotateWebhookSecret']);
        Route::delete('projects/{project}/webhooks/{webhook}', [IdentityProjectController::class, 'destroyWebhook']);
        Route::post('projects/{project}/clients', [IdentityProjectController::class, 'storeClient']);
        Route::post('projects/{project}/clients/{client}/rotate-secret', [IdentityProjectController::class, 'rotateClient']);
        Route::patch('projects/{project}/clients/{client}', [IdentityProjectController::class, 'setClientStatus']);
        Route::put('projects/{project}/clients/{client}/permission-manifest', [IdentityProjectController::class, 'syncManifest']);
        Route::post('projects/{project}/roles', [IdentityProjectController::class, 'storeRole']);
        Route::post('projects/{project}/permissions', [IdentityProjectController::class, 'storePermission']);
        Route::put('projects/{project}/roles/{role}/permissions', [IdentityProjectController::class, 'setRolePermissions']);
        Route::post('projects/{project}/invitations', [IdentityProjectController::class, 'invite']);
        Route::put('projects/{project}/memberships/{membership}/access', [IdentityProjectController::class, 'setMembershipAccess']);
        Route::delete('projects/{project}/memberships/{membership}', [IdentityProjectController::class, 'destroyMembership']);
        Route::get('projects/{project}/audit', [IdentityProjectController::class, 'audit']);
    });
});
