<?php

namespace App\DTO\Responses;

use App\Entity\UserSettings;
use App\Enum\ModelType;

class UserSettingsResponse
{

    public function __construct(
        public readonly ?ModelType $generationModel,
        public readonly ?float $generationCreative,
    ) {}

    public static function fromEntity(UserSettings $userSettings): self
    {
        return new self(
            generationModel: $userSettings->getGenerationModel(),
            generationCreative: $userSettings->getGenerationCreative()
        );
    }
}