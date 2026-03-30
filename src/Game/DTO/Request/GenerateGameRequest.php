<?php

namespace App\Game\DTO\Request;

use App\Shared\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class GenerateGameRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Range(min: 3, max: 80)]
        public int $minAge,

        #[Assert\NotNull]
        #[Assert\Range(min: 3, max: 80)]
        #[Assert\GreaterThan(propertyPath: 'minAge')]
        public int $maxAge,

        #[Assert\NotNull]
        #[Assert\Range(min: 1, max: 100)]
        public int $minPlayers,

        #[Assert\NotNull]
        #[Assert\Range(min: 1, max: 500)]
        #[Assert\GreaterThan(propertyPath: 'minPlayers')]
        public int $maxPlayers,

        #[Assert\NotNull]
        #[Assert\Range(min: 5, max: 480)]
        public int $duration,

        #[Assert\NotNull]
        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public string $locationType,

        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank,
            new Assert\Length(max: 100)
        ])]
        public ?array $requisites = null,

        // Фотографии местности для анализа ИИ
        #[Assert\All([
            new Assert\NotBlank,
            new Assert\Type('string'),
            new Assert\Regex('/^data:image\/(jpeg|png|webp|gif);base64,/')
        ])]
        public array $photos = [],
    ) {}
}