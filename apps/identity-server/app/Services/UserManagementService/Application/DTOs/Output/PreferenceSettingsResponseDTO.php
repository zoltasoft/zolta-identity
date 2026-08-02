<?php

declare(strict_types=1);

namespace App\Services\UserManagementService\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class PreferenceSettingsResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $themePreference,
        public readonly string $languagePreference,
    ) {}

    /**
     * @param  array{user_id:string,theme_preference:string,language_preference:string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['user_id'],
            $payload['theme_preference'],
            $payload['language_preference'],
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'theme_preference' => $this->themePreference,
            'language_preference' => $this->languagePreference,
        ];
    }
}
