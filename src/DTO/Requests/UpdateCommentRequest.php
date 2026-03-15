<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCommentRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 1000)]
        public readonly string $text
    ) {}
}