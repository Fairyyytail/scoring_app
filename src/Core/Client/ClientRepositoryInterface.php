<?php

declare(strict_types=1);

namespace App\Core\Client;

use App\Core\Client\Entity\Client;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use Symfony\Component\Uid\Uuid;

interface ClientRepositoryInterface
{
    public function save(Client $client): void;

    public function flush(): void;

    public function findByPhoneAndEmail(PhoneNumber $phone, Email $email, ?string $excludeId = null): ?Client;

    public function getById(Uuid $id): ?Client;

    /**
     * @return array{items: Client[], total: int}
     */
    public function paginate(int $page, int $perPage): array;

    /**
     * @return iterable{Client[]}
     */
    public function findAll(): iterable;
}
