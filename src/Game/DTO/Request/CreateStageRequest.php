<?php

namespace App\Game\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateStageRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\Length(min: 10)]
        public string $description,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $duration,

        #[Assert\Type('array')]
        public ?array $tasks = [],

        #[Assert\Type('array')]
        public array $props = []
    ) {}
}