<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Invariants;

use InvalidArgumentException;
use Zolta\Domain\Contracts\Invariant;
use Zolta\Domain\Interfaces\VO;

final class RoleNameInvariant extends Invariant
{
    public function ensure(VO $vo, array $options = []): void
    {
        // $v = trim((string)$value);
        // if ($v === '') throw new InvalidArgumentException("$param cannot be empty");
        // if (mb_strlen($v) > 50) throw new InvalidArgumentException("$param cannot exceed 50 chars");
    }
}
