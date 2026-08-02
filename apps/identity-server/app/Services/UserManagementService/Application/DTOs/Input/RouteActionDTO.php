<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

final class RouteActionDTO extends InputDTO
{
    public ?string $userId;

    public string $endpoint;

    public array $routeParams;

    public array $payload;

    public function __construct(
        #[FromRequest('user_id')] ?string $userId = null,
        #[FromRequest('endpoint')] string $endpoint = '',
        #[FromRequest('route_params')] array $routeParams = [],
        #[FromRequest('payload')] array $payload = [],
    ) {
        $this->userId = $userId;
        $this->endpoint = $endpoint;
        $this->routeParams = $routeParams;
        $this->payload = $payload;
    }
}
