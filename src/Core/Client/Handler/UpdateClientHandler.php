<?php

declare(strict_types=1);

namespace App\Core\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Entity\Client;
use App\Core\Client\Service\ScoringService;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use App\Presentation\Request\UpdateClientRequest;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class UpdateClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $repository,
        private ScoringService $scoringService,
    ) {
    }

    public function __invoke(UpdateClientRequest $request): Client
    {
        $phone =  new PhoneNumber($request->phone);
        $email = new Email($request->email);

        $client = $this->repository->getById(Uuid::fromString($request->id))
            ?? throw new InvalidArgumentException('Клиент не найден');

        $duplicate = $this->repository->findByPhoneAndEmail(
            phone: $phone,
            email: $email,
            excludeId: $request->id,
        );

        if (null !== $duplicate) {
            throw new InvalidArgumentException('Пользователь с такими данными уже существует');
        }

        $client->changeFirstName($request->firstName);
        $client->changeLastName($request->lastName);
        $client->changePhone($phone);
        $client->changeEmail($email);
        $client->changeEducation($request->education);
        $client->changePersonalDataConsent($request->personalDataConsent);

        $result = $this->scoringService->calculate($client);
        $client->applyScoring($result->total);

        $this->repository->save($client);
        $this->repository->flush();

        return $client;
    }
}
