<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @template T of object
 */
abstract class DoctrineRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->init();
    }

    protected function init(): void
    {
    }

    protected function entityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function repository(string $entityClass): EntityRepository
    {
        return $this->entityManager()->getRepository($entityClass);
    }

    protected function internalPersist(object $entity): void
    {
        $this->entityManager()->persist($entity);
    }
}
