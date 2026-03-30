<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\Game;
use App\Shared\Enum\GameLocationType;
use App\User\DTO\Response\UserResponse;

readonly class GameResponse
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public ?UserResponse $author,
        public ?int $minAge,
        public ?int $maxAge,
        public ?int $minPlayers,
        public ?int $maxPlayers,
        public ?int $duration,
        public ?GameLocationType $locationType,
        public ?array $photos,
        public ?array $requisites,
        public ?bool $isPublic,
        public ?array $stages,
        public ?int $commentsCount,
        public ?int $likesCount,
        public ?bool $isLiked,
        public string $createdAt,
        public ?string $updatedAt,
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