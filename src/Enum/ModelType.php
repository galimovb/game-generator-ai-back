<?php

namespace App\Enum;

enum ModelType: string
{
    case QWEN2_5_VL_7B = 'qwen/qwen2.5-vl-7b-instruct';
    case QWEN3_VL_8B = 'qwen/qwen3-vl-8b-instruct';
    case QWEN3_VL_8B_THINKING = 'qwen/qwen3-vl-8b-thinking';

    public function getLabel(): string
    {
        return match($this) {
            self::QWEN2_5_VL_7B => 'Qwen2.5-VL 7B (Быстрая)',
            self::QWEN3_VL_8B => 'Qwen3-VL 8B (Сбалансированная)',
            self::QWEN3_VL_8B_THINKING => 'Qwen3-VL 8B Thinking (Качественная)',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}