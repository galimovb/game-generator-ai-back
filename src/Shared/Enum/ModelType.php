<?php

namespace App\Shared\Enum;

enum ModelType: string
{
    case QWEN2_5_VL_7B = 'qwen/qwen2.5-vl-7b-instruct';
    case QWEN3_VL_8B = 'qwen/qwen3-vl-8b-instruct';
    case QWEN3_VL_8B_THINKING = 'qwen/qwen3-vl-8b-thinking';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}