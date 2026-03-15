<?php

namespace App\DTO\Responses;

use App\Entity\Stage;

class StageResponse
{
    public function __construct(
        public readonly int $id,
        public readonly int $order,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?int $duration,
        public readonly ?array $tasks,
        public readonly ?array $props,
        public readonly string $createdAt,
    ) {}

    public static function fromEntity(Stage $stage): self
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