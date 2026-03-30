<?php

namespace App\Support\DTO\Request;

use App\Shared\Enum\TicketStatus;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ChangeTicketStatusRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [TicketStatus::class, 'values'])]
        public string $status
    ) {}
}