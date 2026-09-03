<?php

declare(strict_types=1);

namespace App\Tests\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\DTO\ClientCardDTO;
use App\Core\Client\Entity\Client;
use App\Core\Client\Handler\CardClientHandler;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use App\DataFixtures\ClientFixture;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CardClientHandlerTest extends TestCase
{
    public function testReturnsClientCardDtoWhenClientExists(): void
    {
        [$firstName, $lastName, $phone, $email, $education, $consent] = ClientFixture::FIXED_CLIENTS[ClientFixture::REF_MEGAFON_GMAIL_HIGHER_CONSENT];

        $client = Client::create($firstName, $lastName, new PhoneNumber($phone), new Email($email), $education, $consent);

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->method('getById')->with($client->getId())->willReturn($client);

        $handler = new CardClientHandler($repository);

        $card = ($handler)((string) $client->getId());

        self::assertInstanceOf(ClientCardDTO::class, $card);
        self::assertSame($firstName, $card->firstName);
        self::assertSame($education, $card->education);
    }

    public function testThrowsWhenClientNotFound(): void
    {
        $repository = self::createStub(ClientRepositoryInterface::class);
        $repository->method('getById')->willReturn(null);

        $handler = new CardClientHandler($repository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Клиент не найден');

        ($handler)('bd3d1f94-d857-42db-91a6-42694d0d8e20');
    }
}
