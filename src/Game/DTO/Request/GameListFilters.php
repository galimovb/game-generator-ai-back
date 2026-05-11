<?php

namespace App\Game\DTO\Request;

class GameListFilters
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public ?int $minAge = null,
        public ?int $maxAge = null,
        public ?string $locationType = null,
        public ?string $activityLevel = null,
        public ?int $minPlayers = null,
        public ?int $maxPlayers = null,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'DESC',
    ) {}
}