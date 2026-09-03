<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring;

final readonly class ScoringResult
{
    public function __construct(
        public int $total,
        public array $details,
    ) {
    }
}
