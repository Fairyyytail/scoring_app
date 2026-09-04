<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring\Rule;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\ScoringRuleResult;

final readonly class ConsentScoringRule implements ScoringRuleInterface
{
    public function calculate(Client $client): ScoringRuleResult
    {
        $hasConsent = $client->hasPersonalDataConsent();

        return new ScoringRuleResult(
            reason: $hasConsent ? 'Согласие на обработку ПД: дано' : 'Согласие на обработку ПД: не дано',
            points: $hasConsent ? 4 : 0,
        );
    }
}
