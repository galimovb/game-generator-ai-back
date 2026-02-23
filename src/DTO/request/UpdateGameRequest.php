<?php

namespace App\DTO\request;

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

        //TODO переделать в ENUM
        #[Assert\Choice(choices: ['indoor', 'outdoor', 'both'])]
        public readonly ?string $locationType = null,

        #[Assert\Type('array')]
        public readonly ?array $requisites = null,

        #[Assert\Type('boolean')]
        public readonly ?bool $isPublic = null
    ) {}
}