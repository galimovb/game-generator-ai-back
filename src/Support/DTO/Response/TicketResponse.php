<?php

namespace App\Support\DTO\Response;

use App\Support\Entity\Ticket;
use App\User\DTO\Response\UserResponse;

class TicketResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $subject,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $priority,
        public readonly UserResponse $author,
        public readonly ?UserResponse $assignedTo,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $closedAt,
    ) {}

    public static function fromEntity(Ticket $ticket): self
    {
        return new self(
            id: $ticket->getId(),
            subject: $ticket->getSubject(),
            description: $ticket->getDescription(),
            status: $ticket->getStatus()->value,
            priority: $ticket->getPriority()->value,
            author: UserResponse::fromEntity($ticket->getAuthor()),
            assignedTo: $ticket->getAssignedTo()
                ? UserResponse::fromEntity($ticket->getAssignedTo())
                : null,
            createdAt: $ticket->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $ticket->getUpdatedAt()?->format(DATE_ATOM),
            closedAt: $ticket->getClosedAt()?->format(DATE_ATOM),
        );
    }
}