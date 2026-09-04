<?php

declare(strict_types=1);

namespace App\Core\Client\Enum;

enum MobileOperator: string
{
    case MEGAFON = 'megafon';
    case BEELINE = 'beeline';
    case MTS = 'mts';
    case OTHER = 'other';

    public function translate(): string
    {
        return match ($this) {
            self::MEGAFON => 'МегаФон',
            self::BEELINE => 'Билайн',
            self::MTS => 'МТС',
            self::OTHER => 'Иной',
        };
    }
}
