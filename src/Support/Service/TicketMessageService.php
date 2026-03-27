<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\TicketMessageType;
use App\Shared\Enum\TicketStatus;
use App\Shared\Exception\ApiException;
use App\Support\DTO\Request\CreateTicketMessageRequest;
use App\Support\DTO\Request\UpdateTicketMessageRequest;
use App\Support\Entity\TicketMessage;
use App\Support\Repository\TicketMessageRepository;
use App\Support\Repository\TicketRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketMessageService
{
    public function __construct(
        private readonly TicketRepository $ticketRepo,
        private readonly TicketMessageRepository $messageRepo,
        private readonly EntityManagerInterface $em
    ) {}

    public function createMessage(int $ticketId, CreateTicketMessageRequest $dto, User $user): TicketMessage
    {
        $ticket = $this->ticketRepo->find($ticketId);

        if (!$ticket) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        $isSupport = in_array('ROLE_SUPPORT', $user->getRoles());

        if (!$isSupport && $ticket->getAuthor()->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $message = new TicketMessage();
        $message->setTicket($ticket);
        $message->setOwner($user);
        $message->setText($dto->text);
        $message->setMessageType($isSupport ? TicketMessageType::SUPPORT : TicketMessageType::USER);

        // статусная логика
        if ($isSupport) {
            $ticket->setStatus(
                $ticket->getStatus() === TicketStatus::OPEN
                    ? TicketStatus::IN_PROGRESS
                    : TicketStatus::WAITING_FOR_USER
            );
        } else {
            if ($ticket->getStatus() === TicketStatus::WAITING_FOR_USER) {
                $ticket->setStatus(TicketStatus::IN_PROGRESS);
            }
        }

        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    public function updateMessage(int $id, UpdateTicketMessageRequest $dto, User $user): TicketMessage
    {
        $message = $this->messageRepo->find($id);

        if (!$message) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        if ($message->getOwner()?->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $message->setText($dto->text);
        $message->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();

        return $message;
    }

    public function deleteMessage(int $id, User $user): void
    {
        $message = $this->messageRepo->find($id);

        if (!$message) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        if ($message->getOwner()?->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $this->em->remove($message);
        $this->em->flush();
    }

    public function createSystemMessage($ticket, array $payload): void
    {
        $message = new TicketMessage();
        $message->setTicket($ticket);
        $message->setOwner(null);
        $message->setMessageType(TicketMessageType::SYSTEM);
        $message->setText(json_encode($payload));

        $this->em->persist($message);
    }
}