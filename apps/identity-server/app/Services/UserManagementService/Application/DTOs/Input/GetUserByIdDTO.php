<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Input;

use App\Services\UserManagementService\Application\Attributes\Validation\Required;
use Zolta\Support\Application\Attributes\FromRequest;
use Zolta\Support\Application\DTO\Input\InputDTO;

class GetUserByIdDTO extends InputDTO
{
    final public function __construct(
        #[FromRequest('id')]
        #[Required]
        public readonly string $id,
        public array $options = [],
    ) {}
}
