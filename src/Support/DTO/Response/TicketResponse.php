<?php

namespace App\Support\DTO\Response;

use App\Support\Entity\Ticket;
use App\User\DTO\Response\UserResponse;

readonly class TicketResponse
{
    public function __construct(
        public int $id,
        public string $subject,
        public ?string $description,
        public string $status,
        public string $priority,
        public UserResponse $author,
        public ?UserResponse $assignedTo,
        public string $createdAt,
        public ?string $updatedAt,
        public ?string $closedAt,
    ) {
    }

    public static function fromEntity(Ticket $ticket): self
    {
        return new self(
            id: $ticket->getId(),
            subject: $ticket->getSubject(),
            description: $ticket->getDescription(),
            status: $ticket->getStatus()->value,
            priority: $ticket->getPriority()->value,
            author: UserResponse::fromEntity($ticket->getAuthor()),
            assignedTo: $ticket->getAssignedTo() ? UserResponse::fromEntity($ticket->getAssignedTo()) : null,
            createdAt: $ticket->getCreatedAt()->format('c'),
            updatedAt: $ticket->getUpdatedAt()?->format('c'),
            closedAt: $ticket->getClosedAt()?->format('c'),
        );
    }
}
