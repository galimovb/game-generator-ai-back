<?php

namespace App\Contracts\Repository;

use App\Entity\User;

/**
 * Интерфейс репозитория пользователей
 */
interface UserRepositoryInterface
{
    /**
     * Найти пользователя по ID
     */
    public function findById(int $id): ?User;

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Найти пользователя по логину
     */
    public function findByLogin(string $login): ?User;

    /**
     * Проверить существование email
     */
    public function emailExists(string $email): bool;

    /**
     * Проверить существование логина
     */
    public function loginExists(string $login): bool;

    /**
     * Сохранить пользователя
     */
    public function save(User $user): void;

    /**
     * Удалить пользователя
     */
    public function remove(User $user): void;
}
