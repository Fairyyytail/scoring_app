<?php

declare(strict_types=1);

namespace App\Core\Client\Enum;

enum Education: string
{
    case SECONDARY = 'secondary';
    case VOCATIONAL = 'vocational';
    case HIGHER = 'higher';

    public function translate(): string
    {
        return match ($this) {
            self::SECONDARY => 'Среднее образование',
            self::VOCATIONAL => 'Специальное образование',
            self::HIGHER => 'Высшее образование',
        };
    }
}
