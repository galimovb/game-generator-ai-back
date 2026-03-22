<?php

namespace App\Contracts\Repository;

use App\Entity\Game;
use App\Entity\User;

/**
 * Интерфейс репозитория игр
 */
interface GameRepositoryInterface
{
    /**
     * Найти игру по ID
     */
    public function findById(int $id): ?Game;

    /**
     * Найти игру по ID с подгрузкой этапов
     */
    public function findWithStages(int $id): ?Game;

    /**
     * Получить публичные игры с пагинацией
     * 
     * @return array{items: Game[], total: int}
     */
    public function findPublicGames(int $page, int $limit): array;

    /**
     * Получить игры пользователя с пагинацией
     * 
     * @return array{items: Game[], total: int}
     */
    public function findByAuthor(User $author, int $page, int $limit): array;

    /**
     * Сохранить игру
     */
    public function save(Game $game): void;

    /**
     * Удалить игру
     */
    public function remove(Game $game): void;

    /**
     * Подсчитать количество публичных игр
     */
    public function countPublicGames(): int;

    /**
     * Подсчитать количество игр пользователя
     */
    public function countByAuthor(User $author): int;
}
