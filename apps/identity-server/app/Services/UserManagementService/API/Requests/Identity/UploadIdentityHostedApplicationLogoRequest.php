<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\API\Requests\Identity;

use App\Services\UserManagementService\API\Requests\Concerns\ResolvesAuthenticatedIdentity;
use App\Services\UserManagementService\Application\DTOs\External\UploadedAsset;
use Illuminate\Http\UploadedFile;
use Zolta\Http\Request\BaseRequest;

final class UploadIdentityHostedApplicationLogoRequest extends BaseRequest
{
    use ResolvesAuthenticatedIdentity;

    public function authorize(): bool
    {
        return $this->hasAuthenticatedIdentity();
    }

    /** @return array<string, array<string, bool|string>> */
    public function routeParams(): array
    {
        return [
            'project' => ['type' => 'string', 'required' => true],
            'hosted_application' => ['type' => 'string', 'required' => true],
        ];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project' => ['required', 'uuid'],
            'hosted_application' => ['required', 'uuid'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, mixed> */
    public function trustedData(): array
    {
        $request = request();
        $logo = $request->file('logo');
        if (! $logo instanceof UploadedFile) {
            return [];
        }

        return [
            'actor_user_id' => $this->authenticatedUserId(),
            'project_id' => (string) $request->route('project'),
            'application_id' => (string) $request->route('hosted_application'),
            'logo' => new UploadedAsset(
                path: (string) $logo->getRealPath(),
                originalName: $logo->getClientOriginalName(),
                mimeType: $logo->getMimeType() ?? 'application/octet-stream',
                extension: $logo->extension() ?: 'png',
            ),
        ];
    }
}
