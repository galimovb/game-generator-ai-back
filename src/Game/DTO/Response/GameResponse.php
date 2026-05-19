<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\Game;
use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\GameLocationType;
use App\User\DTO\Response\UserResponse;

readonly class GameResponse
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public ?UserResponse $author,
        public ?int $age,
        public ?int $players,
        public ?int $duration,
        public ?GameLocationType $locationType,
        public ?int $fieldWidth,
        public ?int $fieldLength,
        public ?GameActivityLevel $activityLevel,
        public ?array $photos,
        public ?array $requisites,
        public ?bool $isPublic,
        public ?array $stages,
        public ?int $commentsCount,
        public ?int $likesCount,
        public ?bool $isLiked,
        public string $createdAt,
        public ?string $updatedAt,
        public ?string $locationDescription = null,
    ) {
    }

    public static function fromEntity(
        Game $game,
        bool $isLiked = false,
    ): self {
        return new self(
            id: $game->getId(),
            title: $game->getTitle(),
            description: $game->getDescription(),
            author: $game->getAuthor() ? UserResponse::fromEntity($game->getAuthor()) : null,
            age: $game->getAge(),
            players: $game->getPlayers(),
            duration: $game->getDuration(),
            locationType: $game->getLocationType(),
            fieldWidth: $game->getFieldWidth(),
            fieldLength: $game->getFieldLength(),
            activityLevel: $game->getActivityLevel(),
            photos: $game->getPhotos(),
            requisites: $game->getRequisites(),
            isPublic: $game->isPublic(),
            stages: array_map(
                fn ($stage) => GameStageResponse::fromEntity($stage),
                $game->getStages()->toArray()
            ),
            commentsCount: count($game->getComments()),
            likesCount: count($game->getLikes()),
            isLiked: $isLiked,
            createdAt: $game->getCreatedAt()->format('c'),
            updatedAt: $game->getUpdatedAt()?->format('c'),
            locationDescription: $game->getLocationDescription(),
        );
    }
}
