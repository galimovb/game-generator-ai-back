<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\GameComment;
use App\User\DTO\Response\UserResponse;

readonly class GameCommentResponse
{
    public function __construct(
        public ?int $id,
        public ?int $gameId,
        public ?UserResponse $author,
        public ?string $text,
        public ?int $parentId,
        public string $createdAt,
        public ?string $updatedAt,
    ) {}

    public static function fromEntity(GameComment $gameComment): self
    {
        return new self(
            id: $gameComment->getId(),
            gameId: $gameComment->getGame()->getId(),
            author: UserResponse::fromEntity($gameComment->getAuthor()),
            text: $gameComment->getText(),
            parentId: $gameComment->getParent()?->getId(),
            createdAt: $gameComment->getCreatedAt()->format('c'),
            updatedAt: $gameComment->getUpdatedAt()?->format('c'),
        );
    }
}