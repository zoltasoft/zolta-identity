<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Transformers;

use Zolta\Domain\Contracts\Transformer;

final class EmailNormalizer extends Transformer
{
    public function transform(mixed $value, array $options = []): string
    {

        if ($value === null) {
            return '';
        }
        $s = (string) $value;
        if (($options['trim'] ?? true) === true) {
            $s = trim($s);
        }
        if (($options['lowercase'] ?? true) === true) {
            $s = mb_strtolower($s);
        }

        return $s;
    }
}
