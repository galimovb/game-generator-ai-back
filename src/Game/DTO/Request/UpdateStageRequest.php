<?php

namespace App\Game\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateStageRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $title = null,

        #[Assert\Length(min: 10)]
        public ?string $description = null,

        #[Assert\Positive]
        public ?int $duration = null,

        #[Assert\Type('array')]
        public ?array $tasks = null,

        #[Assert\Type('array')]
        public ?array $props = null
    ) {}
}