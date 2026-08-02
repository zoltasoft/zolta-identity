<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Policies;

use App\Services\UserManagementService\Application\Exceptions\EmailAlreadyInUseException;
use App\Services\UserManagementService\Domain\Exceptions\UserNotFoundException;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\UserId;

final readonly class UserPolicy
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * Ensure the user can update their email.
     *
     * @throws EmailAlreadyInUseException
     * @throws UserNotFoundException
     */
    public function assertCanUpdateEmail(UserId $userId, Email $email): void
    {
        $existingUser = $this->userRepository->findUserByEmail($email);
        if ($existingUser && $existingUser->getId()->get('value') !== $userId->get('value')) {
            throw new EmailAlreadyInUseException;
        }

        $user = $this->userRepository->findUserById($userId);
        if (! $user) {
            throw new UserNotFoundException;
        }
    }

    /**
     * Ensure the user exists by ID or Email.
     *
     * @throws UserNotFoundException
     */
    public function assertExistUser(UserId|Email $identifier): bool
    {
        if ($identifier instanceof UserId) {
            $user = $this->userRepository->findUserById($identifier);
        } elseif ($identifier instanceof Email) {
            $user = $this->userRepository->findUserByEmail($identifier);
        } else {
            throw new \InvalidArgumentException('Unsupported identifier type');
        }

        if (! $user) {
            throw new UserNotFoundException;
        }

        return true;
    }

    /**
     * Ensure the user exists by ID or Email.
     *
     * @throws UserNotFoundException
     */
    public function assertCanRegister(UserId|Email $identifier): bool
    {
        if ($identifier instanceof UserId) {
            $user = $this->userRepository->findUserById($identifier);
        } elseif ($identifier instanceof Email) {
            $user = $this->userRepository->findUserByEmail($identifier);
        } else {
            throw new \InvalidArgumentException('Unsupported identifier type');
        }
        if ($user) {
            throw new EmailAlreadyInUseException;
        }

        return true;
    }
}
