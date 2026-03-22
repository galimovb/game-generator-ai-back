<?php

namespace App\DTO\Responses;

/**
 * Базовый абстрактный класс для всех Response DTO
 */
abstract class AbstractResponseDTO implements \JsonSerializable
{
    /**
     * Формат даты для сериализации
     */
    protected const DATE_FORMAT = 'c';

    /**
     * Преобразование в массив
     */
    abstract public function toArray(): array;

    /**
     * Сериализация в JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Форматирование даты
     */
    protected function formatDate(?\DateTimeInterface $date): ?string
    {
        return $date?->format(self::DATE_FORMAT);
    }

    /**
     * Преобразование сущности в DTO
     * Должен быть реализован в дочерних классах
     */
    abstract public static function fromEntity(object $entity): self;
}
