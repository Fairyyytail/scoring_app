<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring;

final readonly class ScoringRuleResult
{
    public function __construct(
        public string $reason,
        public int $points,
    ) {
    }
}
