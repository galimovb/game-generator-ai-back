<?php

namespace App\Support\DTO\Request;

use App\Shared\Enum\TicketPriority;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeTicketPriorityRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [TicketPriority::class, 'values'])]
    public string $priority;
}