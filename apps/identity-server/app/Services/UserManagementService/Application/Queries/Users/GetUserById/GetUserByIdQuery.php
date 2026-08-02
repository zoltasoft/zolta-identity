<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\Queries\Users\GetUserById;

use Zolta\Cqrs\Queries\Query;
use Zolta\Domain\ValueObjects\UserId;

final class GetUserByIdQuery extends Query
{
    public function __construct(public readonly UserId $id, public array $options = []) {}
}
