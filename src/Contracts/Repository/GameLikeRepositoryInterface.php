<?php

namespace App\Contracts\Repository;

use App\Entity\GameLike;
use App\Entity\User;

/**
 * Интерфейс репозитория лайков игр
 */
interface GameLikeRepositoryInterface
{
    /**
     * Найти лайк по ID
     */
    public function findById(int $id): ?GameLike;

    /**
     * Найти лайк пользователя к конкретной игре
     */
    public function findByUserAndGame(User $user, int $gameId): ?GameLike;

    /**
     * Получить ID игр, которые лайкнул пользователь
     * 
     * @return int[]
     */
    public function findLikedGameIds(User $user, array $gameIds): array;

    /**
     * Получить игры, которые лайкнул пользователь, с пагинацией
     * 
     * @return array{items: GameLike[], total: int}
     */
    public function findByUser(User $user, int $page, int $limit): array;

    /**
     * Сохранить лайк
     */
    public function save(GameLike $like): void;

    /**
     * Удалить лайк
     */
    public function remove(GameLike $like): void;

    /**
     * Подсчитать количество лайков пользователя
     */
    public function countByUser(User $user): int;
}
