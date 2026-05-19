<?php

namespace App\Shared\Enum;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Низкий',
            self::MEDIUM => 'Средний',
            self::HIGH => 'Высокий',
            self::URGENT => 'Срочный',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
