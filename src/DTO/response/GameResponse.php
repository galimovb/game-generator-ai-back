<?php

namespace App\DTO\response;

use App\Entity\Game;
use App\Enum\GameLocationType;

class GameResponse
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly int $authorId,
        public readonly ?int $minAge,
        public readonly ?int $maxAge,
        public readonly ?int $minPlayers,
        public readonly ?int $maxPlayers,
        public readonly ?int $duration,
        public readonly ?GameLocationType $locationType,
        public readonly ?array $photos,
        public readonly ?array $requisites,
        public readonly ?bool $isPublic,
        /** @var StageResponse[] */
        public readonly array $stages,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(Game $game): self
    {
        return new self(
            id: $game->getId(),
            title: $game->getTitle(),
            description: $game->getDescription(),
            authorId: $game->getAuthor()->getId(),
            minAge: $game->getMinAge(),
            maxAge: $game->getMaxAge(),
            minPlayers: $game->getMinPlayers(),
            maxPlayers: $game->getMaxPlayers(),
            duration: $game->getDuration(),
            locationType: $game->getLocationType(),
            photos: $game->getPhotos(),
            requisites: $game->getRequisites(),
            stages: array_map(
                fn($stage) => StageResponse::fromEntity($stage),
                $game->getStages()->toArray()
            ),
            createdAt: $game->getCreatedAt()->format('c'),
            updatedAt: $game->getUpdatedAt()?->format('c'),
            isPublic: $game->isPublic(),
        );
    }
}