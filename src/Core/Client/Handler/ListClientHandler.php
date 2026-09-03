<?php

declare(strict_types=1);

namespace App\Core\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\DTO\ClientCardDTO;
use App\Core\Client\Entity\Client;

final readonly class ListClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $repository,
    ) {
    }

    /**
     * @return array{items: ClientCardDTO[], total: int}
     */
    public function __invoke(int $page, int $perPage): array
    {
        $result = $this->repository->paginate($page, $perPage);

        return [
            'items' => array_map(
                static fn (Client $client): ClientCardDTO => ClientCardDTO::fromEntity($client),
                $result['items'],
            ),
            'total' => $result['total'],
        ];
    }
}
