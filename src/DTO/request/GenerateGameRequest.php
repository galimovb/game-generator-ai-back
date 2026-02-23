<?php

namespace App\DTO\request;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class GenerateGameRequest
{
    /**
     * @param UploadedFile[] $photos
     */
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

        //TODO переделать в ENUM
        #[Assert\Choice(choices: ['indoor', 'outdoor', 'both'])]
        public readonly string $locationType,

        #[Assert\All([
            new Assert\Type('string'),
            new Assert\NotBlank,
            new Assert\Length(max: 100)
        ])]
        public readonly ?array $requisites = null,

        // Фотографии местности для анализа ИИ
        #[Assert\All([
            new Assert\Image(
                maxSize: '10M',
                mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
            )
        ])]
        public readonly array $photos = [],
    ) {}
}