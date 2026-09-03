<?php

declare(strict_types=1);

namespace App\Core\Client\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Сделал VO в DDD стиле, а не ConstraintValidator потому что не нравится размазывать логику по коду без необходимости.
 */
final class Email implements Stringable
{
    private string $value;

    public function __construct(string $rawValue)
    {
        $value = trim($rawValue);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('"%s" не является валидным email', $value));
        }

        $this->value = mb_strtolower($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domainKey(): string
    {
        $host = substr($this->value, strpos($this->value, '@') + 1);

        return mb_strtolower(explode('.', $host)[0]);
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
