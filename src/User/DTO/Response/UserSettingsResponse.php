<?php

namespace App\User\DTO\Response;

use App\Shared\Enum\ModelType;
use App\User\Entity\UserSettings;

readonly class UserSettingsResponse
{

    public function __construct(
        public ?ModelType $generationModel,
        public ?float $generationCreative,
    ) {}

    public static function fromEntity(UserSettings $userSettings): self
    {
        return new self(
            generationModel: $userSettings->getGenerationModel(),
            generationCreative: $userSettings->getGenerationCreative()
        );
    }
}