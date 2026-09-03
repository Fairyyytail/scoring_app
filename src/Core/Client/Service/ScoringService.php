<?php

declare(strict_types=1);

namespace App\Core\Client\Service;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\Rule\ScoringRuleInterface;
use App\Core\Client\Scoring\ScoringResult;

final readonly class ScoringService
{
    /**
     * @param iterable<ScoringRuleInterface> $rules
     */
    public function __construct(
        private iterable $rules,
    ) {
    }

    public function calculate(Client $client): ScoringResult
    {
        $details = [];
        $total = 0;

        foreach ($this->rules as $rule) {
            $result = $rule->calculate($client);

            $details[] = $result;
            $total += $result->points;
        }

        return new ScoringResult($total, $details);
    }
}
