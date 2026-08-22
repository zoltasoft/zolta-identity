<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Commands\Authentication\SocialLogin;

use App\Services\UserManagementService\Application\Payloads\Users\UserPayload;
use App\Services\UserManagementService\Domain\Factories\UserFactory;
use App\Services\UserManagementService\Domain\Repositories\UserRepository;
use Illuminate\Support\Str;
use Zolta\Cqrs\Attributes\HandlesCommand;
use Zolta\Cqrs\Services\Result;
use Zolta\Domain\ValueObjects\Email;
use Zolta\Domain\ValueObjects\Terms;

#[HandlesCommand(ResolveSocialUserCommand::class)]
final readonly class ResolveSocialUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserFactory $userFactory,
    ) {}

    public function __invoke(ResolveSocialUserCommand $resolveSocialUserCommand): Result
    {
        $email = new Email($resolveSocialUserCommand->email);
        $user = $this->userRepository->findUserByEmail($email);

        if (! $user) {
            $user = $this->userFactory->create([
                'username' => $resolveSocialUserCommand->name !== '' ? $resolveSocialUserCommand->name : Str::before($resolveSocialUserCommand->email, '@'),
                'email' => $resolveSocialUserCommand->email,
                'email_verified_at' => new \DateTimeImmutable,
                'password' => Str::random(40),
                'terms' => Terms::accepted,
                'credit' => 0,
            ]);

            $this->userRepository->saveUser($user);
        }

        return Result::success(new UserPayload($user));
    }
}
