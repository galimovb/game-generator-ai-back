<?php

namespace App\DTO\Requests;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Базовый абстрактный класс для всех Request DTO
 */
abstract class AbstractRequestDTO
{
    /**
     * Валидация DTO
     * Может быть переопределена в дочерних классах для кастомной валидации
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
