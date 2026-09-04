<?php

declare(strict_types=1);

namespace App\Core\Client\Scoring;

use App\Core\Client\Enum\MobileOperator;
use App\Core\Client\ValueObject\PhoneNumber;

final readonly class MobileOperatorResolver
{
    private const array CODES = [
        MobileOperator::MEGAFON->value => ['920', '925', '926', '931', '936'],
        MobileOperator::BEELINE->value => ['903', '905', '906', '909', '967'],
        MobileOperator::MTS->value => ['910', '915', '916', '917','983', '985'],
    ];

    public function resolve(PhoneNumber $phoneNumber): MobileOperator
    {
        /**
         * @var string $operator
         */
        foreach (self::CODES as $operator => $codes) {
            if (in_array($phoneNumber->operatorCode(), $codes, true)) {
                return MobileOperator::tryFrom($operator);
            }
        }

        return MobileOperator::OTHER;
    }
}
