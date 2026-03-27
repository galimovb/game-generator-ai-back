<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\GameComment;
use App\User\DTO\Response\UserResponse;

class GameCommentResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $gameId,
        public readonly ?UserResponse $author,
        public readonly ?string $text,
        public readonly ?int $parentId,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
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