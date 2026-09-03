<?php

declare(strict_types=1);

namespace App\Tests\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Handler\UpdateClientHandler;
use App\Core\Client\Scoring\MobileOperatorResolver;
use App\Core\Client\Scoring\Rule\ConsentScoringRule;
use App\Core\Client\Scoring\Rule\EducationScoringRule;
use App\Core\Client\Scoring\Rule\EmailScoringRule;
use App\Core\Client\Scoring\Rule\MobileOperatorScoringRule;
use App\Core\Client\Service\ScoringService;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use App\DataFixtures\ClientFixture;
use App\Presentation\Request\UpdateClientRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateClientHandlerTest extends TestCase
{
    public function testUpdatesClientRecalculatesScoringAndSaves(): void
    {
        $client = $this->buildClient(ClientFixture::REF_MTS_MAIL_SECONDARY_CONSENT);

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('getById')->with($client->getId())->willReturn($client);
        $repository->method('findByPhoneAndEmail')->willReturn(null);
        $repository->expects(self::once())->method('save')->with($client);
        $repository->expects(self::once())->method('flush');

        $handler = new UpdateClientHandler($repository, $this->scoringService());

        [$firstName, $lastName, , $email, , ] = ClientFixture::FIXED_CLIENTS[ClientFixture::REF_MTS_MAIL_SECONDARY_CONSENT];
        [, , $newPhone, , $newEducation, $newConsent] = ClientFixture::FIXED_CLIENTS[ClientFixture::REF_BEELINE_GMAIL_HIGHER_NO_CONSENT];

        $request = new UpdateClientRequest(id: (string) $client->getId());
        $request->firstName = $firstName;
        $request->lastName = $lastName;
        $request->phone = $newPhone;      // Beeline
        $request->email = $email;         // mail-домен из исходного клиента остаётся
        $request->education = $newEducation; // HIGHER
        $request->personalDataConsent = $newConsent;

        $updated = ($handler)($request);

        // Beeline(5) + mail(6) + higher(15) + no consent(0) = 26
        self::assertSame(26, $updated->getScoring());
    }

    public function testUpdatingClientWithUnchangedPhoneAndEmailDoesNotThrow(): void
    {
        $client = $this->buildClient(ClientFixture::REF_MEGAFON_GMAIL_HIGHER_CONSENT);
        [$firstName, $lastName, $phone, $email, $education, $consent] =
            ClientFixture::FIXED_CLIENTS[ClientFixture::REF_MEGAFON_GMAIL_HIGHER_CONSENT];

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('getById')->willReturn($client);
        $repository->expects(self::once())
            ->method('findByPhoneAndEmail')
            ->with(
                self::isInstanceOf(PhoneNumber::class),
                self::isInstanceOf(Email::class),
                $client->getId(),
            )
            ->willReturn(null);
        $repository->expects(self::once())->method('save');

        $handler = new UpdateClientHandler($repository, $this->scoringService());

        $request = new UpdateClientRequest(id: (string) $client->getId());
        $request->firstName = $firstName;
        $request->lastName = $lastName;
        $request->phone = $phone;   // не менялся
        $request->email = $email;   // не менялся
        $request->education = $education;
        $request->personalDataConsent = $consent;

        $updated = ($handler)($request);

        self::assertSame($client, $updated);
    }

    public function testThrowsWhenAnotherClientAlreadyHasThisPhoneOrEmail(): void
    {
        $client = $this->buildClient(ClientFixture::REF_MTS_MAIL_SECONDARY_CONSENT);
        $anotherClient = $this->buildClient(ClientFixture::REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT);

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('getById')->willReturn($client);
        $repository->method('findByPhoneAndEmail')->willReturn($anotherClient); // телефон/email заняты другим клиентом
        $repository->expects(self::never())->method('save');

        $handler = new UpdateClientHandler($repository, $this->scoringService());

        [, , $takenPhone, $takenEmail, , ] = ClientFixture::FIXED_CLIENTS[ClientFixture::REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT];

        $request = new UpdateClientRequest(id: (string) $client->getId());
        $request->firstName = 'Мария';
        $request->lastName = 'Сидорова';
        $request->phone = $takenPhone;
        $request->email = $takenEmail;
        $request->education = Education::HIGHER;
        $request->personalDataConsent = true;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пользователь с такими данными уже существует');

        ($handler)($request);
    }

    public function testThrowsWhenClientNotFound(): void
    {
        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('getById')->willReturn(null);
        $repository->expects(self::never())->method('save');

        $handler = new UpdateClientHandler($repository, $this->scoringService());

        [$firstName, $lastName, $phone, $email, $education, $consent] = ClientFixture::FIXED_CLIENTS[ClientFixture::REF_OTHER_OPERATOR_OTHER_DOMAIN_NO_CONSENT];

        $request = new UpdateClientRequest(id: (string) Uuid::v4());
        $request->firstName = $firstName;
        $request->lastName = $lastName;
        $request->phone = $phone;
        $request->email = $email;
        $request->education = $education;
        $request->personalDataConsent = $consent;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Клиент не найден');

        ($handler)($request);
    }

    private function buildClient(string $reference): Client
    {
        [$firstName, $lastName, $phone, $email, $education, $consent] = ClientFixture::FIXED_CLIENTS[$reference];

        return Client::create($firstName, $lastName, new PhoneNumber($phone), new Email($email), $education, $consent);
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
