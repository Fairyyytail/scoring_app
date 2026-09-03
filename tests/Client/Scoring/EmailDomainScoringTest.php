<?php

declare(strict_types=1);

namespace App\Tests\Client\Scoring;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Scoring\Rule\EmailScoringRule;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailDomainScoringTest extends TestCase
{
    #[DataProvider('emailProvider')]
    public function testCalculatesPointsByDomain(string $email, int $expectedPoints): void
    {
        $rule = new EmailScoringRule();

        $client = Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber('79261234567'),
            email: new Email($email),
            education: Education::VOCATIONAL,
            personalDataConsent: true,
        );

        self::assertSame($expectedPoints, $rule->calculate($client)->points);
    }

    public static function emailProvider(): iterable
    {
        yield 'gmail' => ['user@gmail.com', 10];
        yield 'yandex' => ['user@yandex.ru', 8];
        yield 'mail' => ['user@mail.ru', 6];
        yield 'other' => ['user@corp.io', 3];
    }
}
