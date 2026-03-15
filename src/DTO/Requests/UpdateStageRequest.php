<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateStageRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public readonly ?string $title = null,

        #[Assert\Length(min: 10)]
        public readonly ?string $description = null,

        #[Assert\Positive]
        public readonly ?int $duration = null,

        #[Assert\Type('array')]
        public readonly ?array $tasks = null,

        #[Assert\Type('array')]
        public readonly ?array $props = null
    ) {}
}