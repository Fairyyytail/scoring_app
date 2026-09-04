<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring\Rule;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\ScoringRuleResult;

interface ScoringRuleInterface
{
    public function calculate(Client $client): ScoringRuleResult;
}
