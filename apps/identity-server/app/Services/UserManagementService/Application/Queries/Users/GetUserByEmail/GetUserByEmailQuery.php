<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\GetUserByEmail;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\Email;

final class GetUserByEmailQuery extends Query
{
    public function __construct(public readonly Email $email, public readonly ?array $include = []) {}
}
