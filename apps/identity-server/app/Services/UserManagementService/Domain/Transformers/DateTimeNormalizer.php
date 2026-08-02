<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Transformers;

use DateTimeImmutable;
use Zolta\Domain\Contracts\Transformer;

final class DateTimeNormalizer extends Transformer
{
    public function transform(mixed $value, array $options = []): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }
        $format = $options['format'] ?? null;
        if ($format) {
            $dt = DateTimeImmutable::createFromFormat($format, (string) $value);
            if ($dt === false) {
                throw new \InvalidArgumentException("Invalid date format for value: {$value}");
            }

            return $dt;
        }

        return new DateTimeImmutable((string) $value);
    }
}
