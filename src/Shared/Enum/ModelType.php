<?php

namespace App\Shared\Enum;

enum ModelType: string
{
    case QWEN3_6_27B = 'qwen/qwen3.6-27b';
    case QWEN3_5_27B = 'qwen/qwen3.5-27b';
    case QWEN3_6_PLUS = 'qwen/qwen3.6-plus';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fallbackModel(): string
    {
        return self::QWEN3_6_PLUS->value;
    }
}