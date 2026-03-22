<?php

namespace App\Contracts\Service;

use App\DTO\Requests\UpdateGameRequest;
use App\Entity\Game;
use App\Entity\User;

/**
 * Интерфейс сервиса управления играми (CRUD операции)
 */
interface GameManagementServiceInterface
{
    /**
     * Получить игру по ID
     */
    public function getGame(int $id, ?User $user = null): Game;

    /**
     * Получить публичные игры с пагинацией
     * 
     * @return array{items: array, total: int}
     */
    public function getPublicGames(int $page, int $limit, ?User $user = null): array;

    /**
     * Получить игры пользователя с пагинацией
     * 
     * @return array{items: array, total: int}
     */
    public function getUserGames(User $user, int $page, int $limit): array;

    /**
     * Получить игры, которые лайкнул пользователь
     * 
     * @return array{items: array, total: int}
     */
    public function getUserLikedGames(User $user, int $page, int $limit): array;

    /**
     * Обновить игру
     */
    public function updateGame(int $id, UpdateGameRequest $request, User $user): Game;

    /**
     * Удалить игру
     */
    public function deleteGame(int $id, User $user): void;

    /**
     * Проверить доступ пользователя к игре
     */
    public function checkAccess(Game $game, User $user): void;
}
