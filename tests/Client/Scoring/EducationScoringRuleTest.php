<?php

declare(strict_types=1);

namespace App\Tests\Client\Scoring;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Scoring\Rule\EducationScoringRule;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EducationScoringRuleTest extends TestCase
{
    #[DataProvider('educationProvider')]
    public function testCalculatesPointsByEducation(Education $education, int $expectedPoints): void
    {
        $rule = new EducationScoringRule();

        $client = Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber('79261234567'),
            email: new Email('ivan@gmail.com'),
            education: $education,
            personalDataConsent: true,
        );

        self::assertSame($expectedPoints, $rule->calculate($client)->points);
    }

    public static function educationProvider(): iterable
    {
        yield 'higher' => [Education::HIGHER, 15];
        yield 'vocational' => [Education::VOCATIONAL, 10];
        yield 'secondary' => [Education::SECONDARY, 5];
    }
}
