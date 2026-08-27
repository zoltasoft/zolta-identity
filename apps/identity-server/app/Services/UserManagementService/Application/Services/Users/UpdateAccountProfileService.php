<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Services\Users;

use App\Services\UserManagementService\Application\Commands\Users\UpdateAccountProfile\UpdateAccountProfileCommand;
use App\Services\UserManagementService\Application\DTOs\External\AuthenticatedUser;
use App\Services\UserManagementService\Application\DTOs\Input\UpdateAccountProfileDTO;
use App\Services\UserManagementService\Application\DTOs\Output\AccountProfileResponseDTO;
use App\Services\UserManagementService\Application\Queries\Users\GetUserById\GetUserByIdQuery;
use RuntimeException;
use Zolta\Cqrs\Services\Pipeline\ApplicationService;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;
use Zolta\Domain\ValueObjects\Username;
use Zolta\Support\Application\Attributes\AsApplicationService;

#[AsApplicationService]
final readonly class UpdateAccountProfileService
{
    public function __construct(private ApplicationService $applicationService) {}

    public function __invoke(UpdateAccountProfileDTO $updateAccountProfileDTO): AccountProfileResponseDTO
    {
        $this->applicationService->runAndCapture(UpdateAccountProfileCommand::class, [
            'userId' => new UserId($updateAccountProfileDTO->userId),
            'username' => Username::resolve(['username' => $updateAccountProfileDTO->username]),
            'email' => Email::resolve(['address' => $updateAccountProfileDTO->email]),
            'profilePicture' => $updateAccountProfileDTO->avatarUrl,
        ])->getOrFail();

        $userResult = $this->applicationService->runAndCapture(GetUserByIdQuery::class, [
            'id' => new UserId($updateAccountProfileDTO->userId),
        ])->getOrFail(static fn (): RuntimeException => new RuntimeException('Unable to reload user profile.'));

        $user = $userResult['user'];

        return new AccountProfileResponseDTO(
            AuthenticatedUser::fromDomain($user),
        );
    }
}
