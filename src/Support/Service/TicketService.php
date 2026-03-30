<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\TicketPriority;
use App\Shared\Enum\TicketStatus;
use App\Shared\Exception\ApiException;
use App\Support\DTO\Request\ChangeTicketPriorityRequest;
use App\Support\DTO\Request\ChangeTicketStatusRequest;
use App\Support\DTO\Request\CreateTicketRequest;
use App\Support\Entity\Ticket;
use App\Support\Repository\TicketRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private readonly TicketRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly TicketMessageService $messageService,
        private readonly TicketAccessService $accessService,
    ) {}

    public function createTicket(CreateTicketRequest $request, User $user): Ticket
    {
        $ticket = new Ticket();
        $ticket->setSubject($request->subject);
        $ticket->setDescription($request->description);
        $ticket->setAuthor($user);
        $ticket->setStatus(TicketStatus::OPEN);
        $ticket->setPriority(TicketPriority::MEDIUM);

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    public function getTicketList(User $user, int $page, int $limit, ?string $status = null): array
    {
        $offset = ($page - 1) * $limit;

        $criteria = [];

        if (!$this->accessService->isSupport($user)) {
            $criteria['author'] = $user;
        }

        if ($status !== null) {
            $criteria['status'] = TicketStatus::tryFrom($status);
        }

        $items = $this->repo->findBy(
            $criteria,
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        $total = $this->repo->count($criteria);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    public function getTicket(int $id, User $user): Ticket
    {
        $ticket = $this->accessService->findTicketOrFail($id);

        if (!$this->accessService->canViewTicket($ticket, $user)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        return $ticket;
    }

    public function takeTicket(int $id, User $user): Ticket
    {
        $this->accessService->denyIfNotSupport($user);

        $ticket = $this->accessService->findTicketOrFail($id);

        if ($ticket->getStatus() !== TicketStatus::OPEN &&
            $ticket->getAssignedTo()?->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

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
        $this->accessService->denyIfNotSupport($user);

        $ticket = $this->accessService->findTicketOrFail($id);

        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            throw new ApiException(ErrorCode::TICKET_ALREADY_CLOSED);
        }

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
        $this->accessService->denyIfNotSupport($user);

        $ticket = $this->accessService->findTicketOrFail($id);

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
        $ticket = $this->accessService->findTicketOrFail($id);

        if (!$this->accessService->isSupport($user) &&
            $ticket->getAuthor()->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            throw new ApiException(ErrorCode::TICKET_ALREADY_CLOSED);
        }

        $ticket->setStatus(TicketStatus::CLOSED);
        $ticket->setClosedAt(new \DateTimeImmutable());

        $this->messageService->createSystemMessage($ticket, [
            'event' => 'closed',
            'closedBy' => $user->getId()
        ]);

        $this->em->flush();

        return $ticket;
    }
}