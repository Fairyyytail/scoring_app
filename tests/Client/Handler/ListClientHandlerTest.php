<?php

declare(strict_types=1);

namespace App\Tests\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\DTO\ClientCardDTO;
use App\Core\Client\Entity\Client;
use App\Core\Client\Handler\ListClientHandler;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use App\DataFixtures\ClientFixture;
use PHPUnit\Framework\TestCase;

final class ListClientHandlerTest extends TestCase
{
    public function testReturnsMappedDtosAndTotal(): void
    {
        $clients = array_map(
            static fn (array $data): Client => Client::create(...[
                $data[0], $data[1], new PhoneNumber($data[2]), new Email($data[3]), $data[4], $data[5],
            ]),
            [
                ClientFixture::FIXED_CLIENTS[ClientFixture::REF_MEGAFON_GMAIL_HIGHER_CONSENT],
                ClientFixture::FIXED_CLIENTS[ClientFixture::REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT],
            ],
        );

        $repository = $this->createMock(ClientRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('paginate')
            ->with(2, 20)
            ->willReturn(['items' => $clients, 'total' => 106]); // 6 фиксированных + 100 случайных

        $handler = new ListClientHandler($repository);

        $result = ($handler)(2, 20);

        self::assertSame(106, $result['total']);
        self::assertCount(2, $result['items']);
        self::assertContainsOnlyInstancesOf(ClientCardDTO::class, $result['items']);
        self::assertSame('Иван', $result['items'][0]->firstName);
    }

    public function testReturnsEmptyListWhenNoClients(): void
    {
        $repository = self::createStub(ClientRepositoryInterface::class);
        $repository->method('paginate')->willReturn(['items' => [], 'total' => 0]);

        $handler = new ListClientHandler($repository);

        $result = ($handler)(1, 20);

        self::assertSame(0, $result['total']);
        self::assertSame([], $result['items']);
    }
}
