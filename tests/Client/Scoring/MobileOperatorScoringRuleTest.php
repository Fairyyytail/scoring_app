<?php

declare(strict_types=1);

namespace App\Tests\Client\Scoring;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Scoring\MobileOperatorResolver;
use App\Core\Client\Scoring\Rule\MobileOperatorScoringRule;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MobileOperatorScoringRuleTest extends TestCase
{
    #[DataProvider('phoneProvider')]
    public function testCalculatesPointsByOperator(string $phone, int $expectedPoints): void
    {
        $rule = new MobileOperatorScoringRule(new MobileOperatorResolver());

        $result = $rule->calculate($this->makeClient($phone));

        self::assertSame($expectedPoints, $result->points);
    }

    public static function phoneProvider(): iterable
    {
        yield 'MegaFon' => ['79261234567', 10];
        yield 'Beeline' => ['79031234567', 5];
        yield 'MTS' => ['79831234567', 3];
        yield 'Other' => ['79001234567', 1];
    }

    private function makeClient(string $phone): Client
    {
        return Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber($phone),
            email: new Email('ivan@example.com'),
            education: Education::HIGHER,
            personalDataConsent: false,
        );
    }
}
