<?php

namespace App\Shared\Enum;

enum GameActivityLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function getLabel(): string
    {
        return match($this) {
            self::LOW    => 'Низкая',
            self::MEDIUM => 'Средняя',
            self::HIGH   => 'Высокая',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::LOW    => 'Низкая (спокойные, интеллектуальные игры, минимум бега)',
            self::MEDIUM => 'Средняя (умеренный бег, эстафеты, подвижные игры)',
            self::HIGH   => 'Высокая (интенсивный бег, прыжки, высокая физическая нагрузка)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}