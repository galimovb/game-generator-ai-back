<?php

namespace App\Support\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateTicketMessageRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 5000)]
        public string $text
    ) {}
}