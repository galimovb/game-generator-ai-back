<?php

namespace App\Support\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTicketMessageRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 5000)]
    public string $text;
}