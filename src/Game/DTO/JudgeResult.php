<?php

namespace App\Game\DTO;

readonly class JudgeResult
{
    public function __construct(
        public float $score,
        public bool $passed,
        public bool $isSafe,
        public array $criteria,
        public array $safetyIssues,
        public ?string $failReason,
    ) {
    }
}
