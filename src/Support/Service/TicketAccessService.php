<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\TicketStatus;
use App\Shared\Exception\ApiException;
use App\Support\Entity\Ticket;
use App\Support\Repository\TicketRepository;
use App\User\Entity\User;

class TicketAccessService
{
    public function __construct(
        private readonly TicketRepository $ticketRepo,
    ) {}

    public function findTicketOrFail(int $id): Ticket
    {
        $ticket = $this->ticketRepo->find($id);

        if (!$ticket) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        return $ticket;
    }

    public function isSupport(User $user): bool
    {
        return in_array('ROLE_SUPPORT', $user->getRoles());
    }

    public function canViewTicket(Ticket $ticket, User $user): bool
    {
        if ($this->isSupport($user)) {
            return true;
        }

        return $ticket->getAuthor()->getId() === $user->getId();
    }

    public function canModifyTicket(Ticket $ticket, User $user): bool
    {
        // Для модификации (написать сообщение, изменить статус и т.д.)
        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            return false;
        }

        return $this->canViewTicket($ticket, $user);
    }

    public function denyIfNotSupport(User $user): void
    {
        if (!$this->isSupport($user)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}