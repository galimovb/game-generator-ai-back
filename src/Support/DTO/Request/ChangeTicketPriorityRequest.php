<?php

namespace App\Support\DTO\Request;

use App\Shared\Enum\TicketPriority;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ChangeTicketPriorityRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [TicketPriority::class, 'values'])]
        public string $priority,
    ) {
    }
}
