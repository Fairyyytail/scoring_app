<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring\Rule;

use App\Core\Client\Entity\Client;
use App\Core\Client\Scoring\MobileOperatorResolver;
use App\Core\Client\Scoring\ScoringRuleResult;

final readonly class MobileOperatorScoringRule implements ScoringRuleInterface
{
    private const array POINTS = [
        'megafon' => 10,
        'beeline' => 5,
        'mts' => 3,
        'other' => 1,
    ];

    public function __construct(
        private MobileOperatorResolver $resolver,
    ) {
    }

    public function calculate(Client $client): ScoringRuleResult
    {
        $operator = $this->resolver->resolve($client->getPhone());

        return new ScoringRuleResult(
            reason: sprintf('Сотовый оператор: %s', $operator->translate()),
            points: self::POINTS[$operator->value],
        );
    }
}
