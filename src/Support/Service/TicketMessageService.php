<?php

namespace App\Support\Service;

use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\TicketMessageType;
use App\Shared\Enum\TicketStatus;
use App\Shared\Enum\TicketSystemMessage;
use App\Shared\Enum\UploadType;
use App\Shared\Exception\ApiException;
use App\Shared\Service\UploadService;
use App\Support\DTO\Request\CreateTicketMessageRequest;
use App\Support\DTO\Request\UpdateTicketMessageRequest;
use App\Support\Entity\Ticket;
use App\Support\Entity\TicketMessage;
use App\Support\Repository\TicketMessageRepository;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TicketMessageService
{
    public function __construct(
        private readonly TicketMessageRepository $messageRepo,
        private readonly EntityManagerInterface $em,
        private readonly TicketAccessService $accessService,
        private UploadService $uploadService,
    ) {}

    public function getMessages(int $ticketId, User $user, int $page, int $limit): array
    {
        $ticket = $this->accessService->findTicketOrFail($ticketId);

        if (!$this->accessService->canViewTicket($ticket, $user)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $offset = ($page - 1) * $limit;

        $items = $this->messageRepo->findBy(
            ['ticket' => $ticket],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        $total = $this->messageRepo->count(['ticket' => $ticket]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    public function createMessage(int $ticketId, CreateTicketMessageRequest $dto, User $user): TicketMessage
    {
        $ticket = $this->accessService->findTicketOrFail($ticketId);

        $isSupport = $this->accessService->isSupportOrAdmin($user);

        if (!$isSupport && $ticket->getAuthor()->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            throw new ApiException(ErrorCode::TICKET_ALREADY_CLOSED);
        }

        $message = new TicketMessage();
        $message->setTicket($ticket);
        $message->setAuthor($user);
        $message->setText($dto->text);
        $message->setMessageType($isSupport ? TicketMessageType::SUPPORT : TicketMessageType::USER);

        $this->updateTicketStatusOnMessage($ticket, $isSupport);

        $photos = [];
        foreach ($dto->photos as $photoBase64) {
            $photoPath = $this->uploadService->uploadFromBase64(
                $photoBase64,
                UploadType::TICKET_PHOTO,
                $ticket->getId()
            );
            $photos[] = $photoPath;
        }

        if (!empty($photos)) {
            $message->setPhotos($photos);
        }
        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    public function updateMessage(int $id, UpdateTicketMessageRequest $dto, User $user): TicketMessage
    {
        $message = $this->findMessageOrFail($id);

        if ($message->getAuthor()?->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        if ($message->getMessageType() === TicketMessageType::SYSTEM) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $ticket = $message->getTicket();
        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            throw new ApiException(ErrorCode::TICKET_ALREADY_CLOSED);
        }

        $message->setText($dto->text);

        $this->em->flush();

        return $message;
    }

    public function deleteMessage(int $id, User $user): void
    {
        $message = $this->findMessageOrFail($id);

        if ($message->getAuthor()?->getId() !== $user->getId()) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        if ($message->getMessageType() === TicketMessageType::SYSTEM) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }

        $ticket = $message->getTicket();
        if ($ticket->getStatus() === TicketStatus::CLOSED) {
            throw new ApiException(ErrorCode::TICKET_ALREADY_CLOSED);
        }

        $this->em->remove($message);
        $this->em->flush();
    }

    public function createSystemMessageFromEnum(Ticket $ticket, TicketSystemMessage $event, array $context = []): void
    {
        $text = $event->getText($context);

        $message = new TicketMessage();
        $message->setTicket($ticket);
        $message->setAuthor(null);
        $message->setMessageType(TicketMessageType::SYSTEM);
        $message->setText($text);

        $this->em->persist($message);
    }


    private function findMessageOrFail(int $id): TicketMessage
    {
        $message = $this->messageRepo->find($id);

        if (!$message) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }

        return $message;
    }

    private function updateTicketStatusOnMessage(Ticket $ticket, bool $isSupport): void
    {
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
    }
}