<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\Game;
use App\Shared\Enum\GameLocationType;
use App\User\DTO\Response\UserResponse;

class GameResponse
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?UserResponse $author,
        public readonly ?int $minAge,
        public readonly ?int $maxAge,
        public readonly ?int $minPlayers,
        public readonly ?int $maxPlayers,
        public readonly ?int $duration,
        public readonly ?GameLocationType $locationType,
        public readonly ?array $photos,
        public readonly ?array $requisites,
        public readonly ?bool $isPublic,
        public readonly ?array $stages,
        public readonly ?int $commentsCount,
        public readonly ?int $likesCount,
        public readonly ?bool $isLiked,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(
        Game $game,
        bool $isLiked = false
    ): self {
        return new self(
            id: $game->getId(),
            title: $game->getTitle(),
            description: $game->getDescription(),
            author: UserResponse::fromEntity($game->getAuthor()),
            minAge: $game->getMinAge(),
            maxAge: $game->getMaxAge(),
            minPlayers: $game->getMinPlayers(),
            maxPlayers: $game->getMaxPlayers(),
            duration: $game->getDuration(),
            locationType: $game->getLocationType(),
            photos: $game->getPhotos(),
            requisites: $game->getRequisites(),
            isPublic: $game->isPublic(),
            stages: array_map(
                fn($stage) => GameStageResponse::fromEntity($stage),
                $game->getStages()->toArray()
            ),
            commentsCount: count($game->getComments()), //TODO фикс на производительное решение
            likesCount: count($game->getLikes()),       //TODO фикс на производительное решение
            isLiked: $isLiked,
            createdAt: $game->getCreatedAt()->format('c'),
            updatedAt: $game->getUpdatedAt()?->format('c'),
        );
    }
}