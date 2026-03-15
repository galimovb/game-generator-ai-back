<?php

namespace App\DTO\Requests;

use App\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateGameRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public readonly ?string $title = null,

        #[Assert\Length(min: 10)]
        public readonly ?string $description = null,

        #[Assert\Range(min: 1, max: 18)]
        public readonly ?int $minAge = null,

        #[Assert\Range(min: 1, max: 18)]
        public readonly ?int $maxAge = null,

        #[Assert\Positive]
        public readonly ?int $minPlayers = null,

        #[Assert\Positive]
        public readonly ?int $maxPlayers = null,

        #[Assert\Positive]
        public readonly ?int $duration = null,

        #[Assert\NotNull(message: 'Укажите тип локации')]
        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public readonly string $locationType,

        #[Assert\Type('array')]
        public readonly ?array $requisites = null,

        #[Assert\Type('boolean')]
        public readonly ?bool $isPublic = null
    ) {}
}