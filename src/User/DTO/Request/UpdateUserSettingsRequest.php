<?php

namespace App\User\DTO\Request;

use App\Shared\Enum\ModelType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateUserSettingsRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Choice(callback: [ModelType::class, 'values'])]
        public string $generationModel,

        #[Assert\NotNull]
        #[Assert\Range(min: 0, max: 1)]
        public float $generationCreative,
    ) {}
}