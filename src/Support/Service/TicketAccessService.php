<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Exception\ApiException;
use App\Support\Entity\Ticket;
use App\Support\Repository\TicketRepository;
use App\User\Entity\User;

class TicketAccessService
{
    public function __construct(
        private readonly TicketRepository $ticketRepo,
    ) {
    }

    public function findTicketOrFail(int $id): Ticket
    {
        $ticket = $this->ticketRepo->find($id);

        if (!$ticket) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        return $ticket;
    }

    public function isSupportOrAdmin(User $user): bool
    {
        return in_array('ROLE_SUPPORT', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles());
    }

    public function canViewTicket(Ticket $ticket, User $user): bool
    {
        if ($this->isSupportOrAdmin($user)) {
            return true;
        }

        return $ticket->getAuthor()->getId() === $user->getId();
    }

    public function denyIfNotSupport(User $user): void
    {
        if (!$this->isSupportOrAdmin($user)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}
