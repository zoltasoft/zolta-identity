<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Invariants;

use Zolta\Domain\Contracts\Invariant;
use Zolta\Domain\Interfaces\VO;
use Zolta\Domain\ValueObjects\Email;

final class EmailVOInvariant extends Invariant
{
    public function ensure(VO $vo, array $options = []): void
    {
        $this->assertVOType($vo, Email::class);
        /** @var Email $vo */

        // default: do not allow verifiedAt in future
        $allowFuture = $options['allowFutureVerified'] ?? false;
        $verifiedAt = $vo->get('verifiedAt');
        if ($verifiedAt !== null && ! $allowFuture && $verifiedAt > new \DateTimeImmutable) {
            $this->throwIf(true, 'verifiedAt cannot be in the future');
        }

        // optional example cross-attribute: if domain requires verification, ensure verifiedAt present
        $domainRequiresVerification = $options['domainRequiresVerified'] ?? null;
        if (is_array($domainRequiresVerification)) {
            $addr = $vo->get('address');
            $domain = (str_contains($addr, '@')) ? substr(strrchr($addr, '@'), 1) : '';
            if (in_array($domain, $domainRequiresVerification, true) && $verifiedAt === null) {
                $this->throwIf(true, "Email from domain {$domain} must be verified");
            }
        }
    }
}
