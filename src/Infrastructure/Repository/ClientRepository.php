<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Entity\Client;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Uid\Uuid;

/**
 * @extends DoctrineRepository<Client>
 */
class ClientRepository extends DoctrineRepository implements ClientRepositoryInterface
{
    public function save(Client $client): void
    {
        $this->internalPersist($client);
    }

    public function flush(): void
    {
        $this->entityManager()->flush();
    }

    public function findByPhoneAndEmail(PhoneNumber $phone, Email $email, ?string $excludeId = null): ?Client
    {
        $qb = $this->entityManager()->createQueryBuilder();

        $qb->select('c')
            ->from(Client::class, 'c')
            ->where('c.phone = :phone OR c.email = :email')
            ->setParameter('phone', $phone->value())
            ->setParameter('email', $email->value());

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId, 'uuid');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getById(Uuid $id): ?Client
    {
        return $this->repository(Client::class)->findOneBy(['id' => $id]);
    }

    public function paginate(int $page, int $perPage): array
    {
        $qb = $this->entityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(Client::class, 'c')
            ->orderBy('c.registeredAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb);

        return [
            'items' => iterator_to_array($paginator),
            'total' => count($paginator),
        ];
    }

    public function findAll(): iterable
    {
        $qb = $this->entityManager()->createQueryBuilder();

        $qb->select('c')
            ->from(Client::class, 'c');

        return $qb->getQuery()->toIterable();
    }
}
