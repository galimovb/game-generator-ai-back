<?php

namespace App\Game\DTO\Request;

use App\Shared\Enum\GameLocationType;
use Symfony\Component\Validator\Constraints as Assert;

class GenerateGameRequest
{
    public function __construct(
        #[Assert\NotNull(message: 'Укажите минимальный возраст')]
        #[Assert\Range(min: 3, max: 80)]
        public readonly int $minAge,

        #[Assert\NotNull(message: 'Укажите максимальный возраст')]
        #[Assert\Range(min: 3, max: 80)]
        #[Assert\GreaterThan(propertyPath: 'minAge')]
        public readonly int $maxAge,

        #[Assert\NotNull(message: 'Укажите минимальное количество игроков')]
        #[Assert\Range(min: 1, max: 100)]
        public readonly int $minPlayers,

        #[Assert\NotNull(message: 'Укажите максимальное количество игроков')]
        #[Assert\Range(min: 1, max: 500)]
        #[Assert\GreaterThan(propertyPath: 'minPlayers')]
        public readonly int $maxPlayers,

        #[Assert\NotNull(message: 'Укажите длительность игры')]
        #[Assert\Range(min: 5, max: 480)]
        public readonly int $duration,

        #[Assert\NotNull(message: 'Укажите тип локации')]
        #[Assert\Choice(callback: [GameLocationType::class, 'values'])]
        public readonly string $locationType,

        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank,
            new Assert\Length(max: 100)
        ])]
        public readonly ?array $requisites = null,

        // Фотографии местности для анализа ИИ
        #[Assert\All([
            new Assert\NotBlank,
            new Assert\Type('string'),
            new Assert\Regex('/^data:image\/(jpeg|png|webp|gif);base64,/')
        ])]
        public readonly array $photos = [],
    ) {}
}