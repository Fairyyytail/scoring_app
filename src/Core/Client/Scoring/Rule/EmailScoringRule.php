<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring\Rule;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\ScoringRuleResult;

final class EmailScoringRule implements ScoringRuleInterface
{
    private const array POINTS = [
        'gmail' => 10,
        'yandex' => 8,
        'mail' => 6,
    ];
    private const int DEFAULT_POINTS = 3;

    public function calculate(Client $client): ScoringRuleResult
    {
        $domainKey = $client->getEmailDomainKey();

        return new ScoringRuleResult(
            reason: sprintf('Домен e-mail: %s', array_key_exists($domainKey, self::POINTS) ? $domainKey : 'иной'),
            points: self::POINTS[$domainKey] ?? self::DEFAULT_POINTS,
        );
    }
}
