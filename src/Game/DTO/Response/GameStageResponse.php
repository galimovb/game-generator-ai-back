<?php

namespace App\Game\DTO\Response;

use App\Game\Entity\GameStage;

readonly class GameStageResponse
{
    public function __construct(
        public int $id,
        public int $order,
        public ?string $title,
        public ?string $description,
        public ?int $duration,
        public ?array $tasks,
        public ?array $props,
        public string $createdAt,
    ) {}

    public static function fromEntity(GameStage $stage): self
    {
        return new self(
            id: $stage->getId(),
            order: $stage->getStageOrder() ?? 0,
            title: $stage->getTitle(),
            description: $stage->getDescription(),
            duration: $stage->getDuration(),
            tasks: $stage->getTasks(),
            props: $stage->getProps(),
            createdAt: $stage->getCreatedAt()?->format('c'),
        );
    }
}