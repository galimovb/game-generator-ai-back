<?php

namespace App\User\DTO\Response;

use App\Shared\Enum\ModelType;
use App\User\Entity\UserSettings;

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