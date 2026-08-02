<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Domain\Exceptions;

use DomainException;

final class InvalidIdentityWebhookConfigurationException extends DomainException {}
