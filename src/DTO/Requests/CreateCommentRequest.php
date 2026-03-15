<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 1000)]
        public readonly string $text,

        #[Assert\Positive]
        public readonly ?int $parentId = null
    ) {}
}