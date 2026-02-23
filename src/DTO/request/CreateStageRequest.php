<?php

namespace App\DTO\request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateStageRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $gameId,

        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public readonly string $title,

        #[Assert\NotBlank]
        #[Assert\Length(min: 10)]
        public readonly string $description,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $duration,

        #[Assert\Type('array')]
        public readonly array $tasks = [],

        #[Assert\Type('array')]
        public readonly array $props = []
    ) {}
}