<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring\Rule;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\ScoringRuleResult;

final class EducationScoringRule implements ScoringRuleInterface
{
    private const array POINTS = [
        'higher' => 15,
        'vocational' => 10,
        'secondary' => 5,
    ];

    public function calculate(Client $client): ScoringRuleResult
    {
        $education = $client->getEducation();

        return new ScoringRuleResult(
            reason: sprintf('Образование: %s', $education->translate()),
            points: self::POINTS[$education->value],
        );
    }
}
