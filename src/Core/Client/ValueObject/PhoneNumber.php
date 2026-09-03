<?php

declare(strict_types=1);

namespace App\Core\Client\ValueObject;

use InvalidArgumentException;
use Stringable;

final class PhoneNumber implements Stringable
{
    private const string PATTERN = '/^7\d{10}$/';

    private string $value;

    public function __construct(string $rawValue)
    {
        $digits = preg_replace('/\D/', '', $rawValue) ?? '';

        if (11 === strlen($digits) && '8' === $digits[0]) {
            $digits = '7'.substr($digits, 1);
        }

        if (10 === strlen($digits)) {
            $digits = '7'.$digits;
        }

        if (!preg_match(self::PATTERN, $digits)) {
            throw new InvalidArgumentException(sprintf('"%s" не является валидным номером телефона российского образца.', $rawValue));
        }

        $this->value = $digits;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function formatted(): string
    {
        return sprintf(
            '+%s (%s) %s-%s-%s',
            $this->value[0],
            substr($this->value, 1, 3),
            substr($this->value, 4, 3),
            substr($this->value, 7, 2),
            substr($this->value, 9, 2),
        );
    }

    public function operatorCode(): string
    {
        return substr($this->value, 1, 3);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
