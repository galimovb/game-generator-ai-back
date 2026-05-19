<?php

namespace App\Support\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateTicketRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $subject,

        #[Assert\NotBlank]
        #[Assert\Length(max: 5000)]
        public string $description,
    ) {
    }
}
