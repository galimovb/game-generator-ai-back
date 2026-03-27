<?php

namespace App\User\DTO\Request;

use App\Shared\Enum\ModelType;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserSettingsRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Choice(callback: [ModelType::class, 'values'])]
        public readonly string $generationModel,

        #[Assert\NotNull]
        #[Assert\Range(min: 0, max: 1)]
        public readonly float $generationCreative,
    ) {}
}