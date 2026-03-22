<?php

namespace App\Contracts\Service;

use App\Entity\GameLike;
use App\Entity\User;

/**
 * Интерфейс сервиса управления лайками игр
 */
interface GameLikeServiceInterface
{
    /**
     * Добавить лайк игре
     */
    public function addLike(int $gameId, User $user): GameLike;

    /**
     * Удалить лайк
     */
    public function removeLike(int $gameId, User $user): void;

    /**
     * Проверить, лайкнул ли пользователь игру
     */
    public function isLikedByUser(int $gameId, User $user): bool;

    /**
     * Получить ID игр, которые лайкнул пользователь
     * 
     * @return int[]
     */
    public function getLikedGameIds(User $user, array $gameIds): array;

    /**
     * Прикрепить информацию о лайках к списку игр
     * 
     * @param Game[] $games
     * @return array Массив с элементами ['game' => Game, 'isLiked' => bool]
     */
    public function attachLikeInfo(array $games, ?User $user): array;
}
