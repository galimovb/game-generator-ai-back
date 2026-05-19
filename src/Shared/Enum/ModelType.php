<?php

namespace App\Shared\Enum;

enum ModelType: string
{
    case QWEN3_6_27B = 'qwen/qwen3.6-27b';
    case QWEN3_5_27B = 'qwen/qwen3.5-27b';
    case QWEN3_6_PLUS = 'qwen/qwen3.6-plus';

    case QWEN3_5_9B = 'qwen/qwen3.5-9b';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fallbackModel(): string
    {
        return self::QWEN3_6_27B->value;
    }

    public static function validationModel(): string
    {
        return self::QWEN3_5_9B->value;
    }
}