<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class RouteActionResponseDTO extends ResponseDTO
{
    public array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}
