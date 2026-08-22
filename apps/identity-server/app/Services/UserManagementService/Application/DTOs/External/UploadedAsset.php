<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\External;

final readonly class UploadedAsset
{
    public function __construct(
        public string $path,
        public string $originalName,
        public string $mimeType,
        public string $extension,
    ) {}
}
