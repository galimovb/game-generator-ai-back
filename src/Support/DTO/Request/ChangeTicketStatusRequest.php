<?php

namespace App\Support\DTO\Request;

use App\Shared\Enum\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeTicketStatusRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [TicketStatus::class, 'values'])]
    public string $status;
}