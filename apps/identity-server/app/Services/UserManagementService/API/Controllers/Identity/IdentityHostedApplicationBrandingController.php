<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Controllers\Identity;

use App\Services\UserManagementService\Application\Contracts\Identity\Projects\ManageIdentityHostedApplications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Router\Attributes\Route;

final class IdentityHostedApplicationBrandingController extends Controller
{
    private const MIDDLEWARE = ['api', 'auth:sanctum', 'identity.token'];

    public function __construct(private readonly ManageIdentityHostedApplications $applications) {}

    #[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}/logo', methods: ['POST'], middleware: self::MIDDLEWARE, name: 'identity.projects.hosted_applications.logo.store')]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validated = $validator->validated();
        $actor = (string) $request->user('sanctum')?->getAuthIdentifier();
        $projectId = (string) $request->route('project');
        $applicationId = (string) $request->route('hosted_application');

        return response()->json([
            'data' => $this->applications->uploadHostedApplicationLogo(
                $actor,
                $projectId,
                $applicationId,
                $validated['logo'],
            ),
        ], 201);
    }

    #[Route('v1/identity/projects/{project}/hosted-applications/{hosted_application}/logo', methods: ['DELETE'], middleware: self::MIDDLEWARE, name: 'identity.projects.hosted_applications.logo.destroy')]
    public function destroy(Request $request): JsonResponse
    {
        $actor = (string) $request->user('sanctum')?->getAuthIdentifier();
        $this->applications->removeHostedApplicationLogo(
            $actor,
            (string) $request->route('project'),
            (string) $request->route('hosted_application'),
        );

        return response()->json(['data' => ['message' => 'Hosted application logo removed.']]);
    }
}
