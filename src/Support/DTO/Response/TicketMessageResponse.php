<?php

namespace App\Support\DTO\Response;

use App\Support\Entity\TicketMessage;
use App\User\DTO\Response\UserResponse;

readonly class TicketMessageResponse
{
    public function __construct(
        public int           $id,
        public ?string       $text,
        public ?array        $photos,
        public string        $messageType,
        public ?UserResponse $author,
        public string        $createdAt,
        public ?string       $updatedAt,
    ) {}

    public static function fromEntity(TicketMessage $message): self
    {
        return new self(
            id: $message->getId(),
            text: $message->getText(),
            photos: $message->getPhotos(),
            messageType: $message->getMessageType()->value,
            author: $message->getAuthor()
                ? UserResponse::fromEntity($message->getAuthor())
                : null,
            createdAt: $message->getCreatedAt()->format('c'),
            updatedAt: $message->getUpdatedAt()?->format('c'),
        );
    }
}