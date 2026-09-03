<?php

declare(strict_types=1);

namespace App\Tests\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Entity\Client;
use App\Core\Client\Handler\RegisterClientHandler;
use App\Core\Client\Scoring\MobileOperatorResolver;
use App\Core\Client\Scoring\Rule\ConsentScoringRule;
use App\Core\Client\Scoring\Rule\EducationScoringRule;
use App\Core\Client\Scoring\Rule\EmailScoringRule;
use App\Core\Client\Scoring\Rule\MobileOperatorScoringRule;
use App\Core\Client\Service\ScoringService;
use App\DataFixtures\ClientFixture;
use App\Presentation\Request\RegisterClientRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RegisterClientHandlerTest extends TestCase
{
    public function testRegistersClientCalculatesScoringAndSaves(): void
    {
        [$firstName, $lastName, $phone, $email, $education, $consent] =
            ClientFixture::FIXED_CLIENTS[ClientFixture::REF_MEGAFON_GMAIL_HIGHER_CONSENT];

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('findByPhoneAndEmail')->willReturn(null);
        $repository->expects(self::once())->method('save')->with(self::isInstanceOf(Client::class));
        $repository->expects(self::once())->method('flush');

        $handler = new RegisterClientHandler($repository, $this->scoringService());

        $request = new RegisterClientRequest();
        $request->firstName = $firstName;
        $request->lastName = $lastName;
        $request->phone = $phone;
        $request->email = $email;
        $request->education = $education;
        $request->personalDataConsent = $consent;

        $client = ($handler)($request);

        // MegaFon(10) + gmail(10) + higher(15) + consent(4) = 39, см. комментарий в фикстуре
        self::assertSame(39, $client->getScoring());
        self::assertNotNull($client->getScoringCalculatedAt());
    }

    public function testThrowsWhenClientWithSamePhoneOrEmailAlreadyExists(): void
    {
        [$firstName, $lastName, $phone, $email, $education, $consent] =
            ClientFixture::FIXED_CLIENTS[ClientFixture::REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT];

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('findByPhoneAndEmail')->willReturn(self::createStub(Client::class));
        $repository->expects(self::never())->method('save');

        $handler = new RegisterClientHandler($repository, $this->scoringService());

        $request = new RegisterClientRequest();
        $request->firstName = $firstName;
        $request->lastName = $lastName;
        $request->phone = $phone;
        $request->email = $email;
        $request->education = $education;
        $request->personalDataConsent = $consent;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Клиент уже зарегестрирован');

        ($handler)($request);
    }

    private function scoringService(): ScoringService
    {
        return new ScoringService([
            new MobileOperatorScoringRule(new MobileOperatorResolver()),
            new EmailScoringRule(),
            new EducationScoringRule(),
            new ConsentScoringRule(),
        ]);
    }
}
