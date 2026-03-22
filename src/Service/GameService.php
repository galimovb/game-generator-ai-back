<?php

namespace App\Service;

use App\Contracts\Service\GameGeneratorServiceInterface;
use App\Contracts\Service\GameLikeServiceInterface;
use App\Contracts\Service\GameManagementServiceInterface;
use App\DTO\Requests\GenerateGameRequest;
use App\DTO\Requests\UpdateGameRequest;
use App\Entity\Game;
use App\Entity\User;

/**
 * Фасадный сервис для работы с играми
 * Делегирует вызовы специализированным сервисам
 * 
 * @deprecated Используйте специализированные сервисы:
 * @see GameGeneratorServiceInterface
 * @see GameManagementServiceInterface
 * @see GameLikeServiceInterface
 */
class GameService
{
    public function __construct(
        private readonly GameGeneratorServiceInterface $gameGeneratorService,
        private readonly GameManagementServiceInterface $gameManagementService,
        private readonly GameLikeServiceInterface $gameLikeService,
    ) {}

    /**
     * Сгенерировать и сохранить игру
     */
    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        return $this->gameGeneratorService->generateAndSave($request, $author);
    }

    /**
     * Получить публичные игры
     * 
     * @return array{items: array, total: int}
     */
    public function getPublicGames(int $page, int $limit, ?User $user = null): array
    {
        return $this->gameManagementService->getPublicGames($page, $limit, $user);
    }

    /**
     * Получить игры, которые лайкнул пользователь
     * 
     * @return array{items: array, total: int}
     */
    public function getUserLikeGames(User $user, int $page, int $limit): array
    {
        return $this->gameManagementService->getUserLikedGames($user, $page, $limit);
    }

    /**
     * Получить игры пользователя
     * 
     * @return array{items: array, total: int}
     */
    public function getUserGames(User $user, int $page, int $limit): array
    {
        return $this->gameManagementService->getUserGames($user, $page, $limit);
    }

    /**
     * Получить игру по ID
     */
    public function getGame(int $id, ?User $user = null): Game
    {
        return $this->gameManagementService->getGame($id, $user);
    }

    /**
     * Обновить игру
     */
    public function updateGame(int $id, UpdateGameRequest $request, User $user): Game
    {
        return $this->gameManagementService->updateGame($id, $request, $user);
    }

    /**
     * Удалить игру
     */
    public function deleteGame(int $id, User $user): void
    {
        $this->gameManagementService->deleteGame($id, $user);
    }

    /**
     * Прикрепить информацию о лайках к списку игр
     */
    public function attachLikeInfo(array $games, ?User $user): array
    {
        return $this->gameLikeService->attachLikeInfo($games, $user);
    }
}
