<?php

declare(strict_types=1);

namespace App\Tests\Client\Scoring;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Scoring\MobileOperatorResolver;
use App\Core\Client\Scoring\Rule\ConsentScoringRule;
use App\Core\Client\Scoring\Rule\EducationScoringRule;
use App\Core\Client\Scoring\Rule\EmailScoringRule;
use App\Core\Client\Scoring\Rule\MobileOperatorScoringRule;
use App\Core\Client\Service\ScoringService;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class ScoringServiceTest extends TestCase
{
    public function testCalculateSumsPointsFromAllRulesAndKeepsDetails(): void
    {
        $service = new ScoringService([
            new MobileOperatorScoringRule(new MobileOperatorResolver()),
            new EmailScoringRule(),
            new EducationScoringRule(),
            new ConsentScoringRule(),
        ]);

        $client = Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber('89833873699'),
            email: new Email('ivan@gmail.com'),
            education: Education::HIGHER,
            personalDataConsent: true,
        );

        $result = $service->calculate($client);

        // MTS(3) + gmail(10) + higher(15) + consent(4) = 32
        self::assertSame(32, $result->total);
        self::assertCount(4, $result->details);
    }

    public function testCalculateIsPureAndDoesNotMutateClient(): void
    {
        $service = new ScoringService([new ConsentScoringRule()]);

        $client = Client::create(
            firstName: 'Иван',
            lastName: 'Иванов',
            phone: new PhoneNumber('89833873699'),
            email: new Email('ivan@gmail.com'),
            education: Education::HIGHER,
            personalDataConsent: false,
        );

        $service->calculate($client);

        self::assertSame(0, $client->getScoring());
        self::assertNull($client->getScoringCalculatedAt());
    }
}
