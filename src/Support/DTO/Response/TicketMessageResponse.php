<?php

namespace App\Support\DTO\Response;

use App\Shared\Enum\TicketMessageType;
use App\Support\Entity\TicketMessage;
use App\User\DTO\Response\UserResponse;

class TicketMessageResponse
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $text,
        public readonly ?array $photos,
        public readonly string $messageType,
        public readonly ?UserResponse $owner,
        public readonly ?array $systemPayload,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(TicketMessage $message): self
    {
        return new self(
            id: $message->getId(),
            text: $message->getText(),
            photos: $message->getPhotos(),
            messageType: $message->getMessageType()->value,
            owner: $message->getOwner()
                ? UserResponse::fromEntity($message->getOwner())
                : null,
            systemPayload: $message->getMessageType() === TicketMessageType::SYSTEM
                ? json_decode($message->getText(), true)
                : null,
            createdAt: $message->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $message->getUpdatedAt()?->format(DATE_ATOM),
        );
    }
}