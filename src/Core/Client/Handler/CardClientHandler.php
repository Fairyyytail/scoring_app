<?php

declare(strict_types=1);

namespace App\Core\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\DTO\ClientCardDTO;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class CardClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $repository,
    ) {
    }

    public function __invoke(string $id): ClientCardDTO
    {
        $client =  $this->repository->getById(Uuid::fromString($id)) ?? throw new InvalidArgumentException('Клиент не найден');

        return ClientCardDTO::fromEntity($client);
    }
}
