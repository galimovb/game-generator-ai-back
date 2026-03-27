<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\TicketPriority;
use App\Shared\Enum\TicketStatus;
use App\Shared\Exception\ApiException;
use App\Support\DTO\Request\ChangeTicketPriorityRequest;
use App\Support\DTO\Request\ChangeTicketStatusRequest;
use App\Support\Entity\Ticket;
use App\Support\Repository\TicketRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private readonly TicketRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly TicketMessageService $messageService
    ) {}

    public function takeTicket(int $id, User $user): Ticket
    {
        $this->denySupport($user);

        $ticket = $this->findOrFail($id);

        $ticket->setAssignedTo($user);
        $ticket->setStatus(TicketStatus::IN_PROGRESS);

        $this->messageService->createSystemMessage($ticket, [
            'event' => 'assigned',
            'userId' => $user->getId()
        ]);

        $this->em->flush();

        return $ticket;
    }

    public function changeStatus(int $id, ChangeTicketStatusRequest $dto, User $user): Ticket
    {
        $this->denySupport($user);

        $ticket = $this->findOrFail($id);

        $old = $ticket->getStatus();
        $new = TicketStatus::from($dto->status);

        $ticket->setStatus($new);

        $this->messageService->createSystemMessage($ticket, [
            'event' => 'status_changed',
            'from' => $old->value,
            'to' => $new->value
        ]);

        $this->em->flush();

        return $ticket;
    }

    public function changePriority(int $id, ChangeTicketPriorityRequest $dto, User $user): Ticket
    {
        $this->denySupport($user);

        $ticket = $this->findOrFail($id);

        $old = $ticket->getPriority();
        $new = TicketPriority::from($dto->priority);

        $ticket->setPriority($new);

        $this->messageService->createSystemMessage($ticket, [
            'event' => 'priority_changed',
            'from' => $old->value,
            'to' => $new->value
        ]);

        $this->em->flush();

        return $ticket;
    }

    public function closeTicket(int $id, User $user): Ticket
    {
        $ticket = $this->findOrFail($id);

        if ($ticket->getAuthor()->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $ticket->setStatus(TicketStatus::CLOSED);
        $ticket->setClosedAt(new \DateTimeImmutable());

        $this->messageService->createSystemMessage($ticket, [
            'event' => 'closed'
        ]);

        $this->em->flush();

        return $ticket;
    }

    private function findOrFail(int $id): Ticket
    {
        $ticket = $this->repo->find($id);

        if (!$ticket) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        return $ticket;
    }

    private function denySupport(User $user): void
    {
        if (!in_array('ROLE_SUPPORT', $user->getRoles())) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }
}