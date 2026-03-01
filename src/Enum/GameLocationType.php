<?php

namespace App\Enum;

enum GameLocationType: string
{
    case INDOOR = 'indoor';
    case OUTDOOR = 'outdoor';
    case BOTH = 'both';

    public function getValue():string
    {
        return match($this) {
            self::INDOOR => 'В помещении',
            self::OUTDOOR => 'На улице',
            self::BOTH => 'Совмещенно',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}