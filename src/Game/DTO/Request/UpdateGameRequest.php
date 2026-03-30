<?php

namespace App\Game\DTO\Request;

use App\Shared\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateGameRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $title = null,

        #[Assert\Length(min: 10)]
        public ?string $description = null,

        #[Assert\Range(min: 1, max: 18)]
        public ?int $minAge = null,

        #[Assert\Range(min: 1, max: 18)]
        public ?int $maxAge = null,

        #[Assert\Positive]
        public ?int $minPlayers = null,

        #[Assert\Positive]
        public ?int $maxPlayers = null,

        #[Assert\Positive]
        public ?int $duration = null,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public string $locationType,

        #[Assert\Type('array')]
        public ?array $requisites = null,

        #[Assert\Type('boolean')]
        public ?bool $isPublic = null
    ) {}
}