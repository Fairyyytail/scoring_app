<?php

declare(strict_types=1);

namespace App\Tests\Client\Scoring;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Scoring\Rule\ConsentScoringRule;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConsentScoringRuleTest extends TestCase
{
    #[DataProvider('consentProvider')]
    public function testCalculatesPointsByConsent(bool $consent, int $expectedPoints): void
    {
        $rule = new ConsentScoringRule();
        $client = Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber('79261234567'),
            email: new Email('ivan@gmail.com'),
            education: Education::HIGHER,
            personalDataConsent: $consent,
        );
        self::assertSame($expectedPoints, $rule->calculate($client)->points);
    }

    public static function consentProvider(): iterable
    {
        yield 'given' => [true, 4];
        yield 'not given' => [false, 0];
    }
}
