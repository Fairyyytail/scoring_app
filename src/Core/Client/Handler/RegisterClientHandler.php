<?php

declare(strict_types=1);

namespace App\Core\Client\Handler;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Entity\Client;
use App\Core\Client\Service\ScoringService;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use App\Presentation\Request\RegisterClientRequest;
use InvalidArgumentException;

final readonly class RegisterClientHandler
{
    public function __construct(
        private ClientRepositoryInterface $repository,
        private ScoringService $scoringService,
    ) {
    }

    public function __invoke(RegisterClientRequest $request): Client
    {
        $phone =  new PhoneNumber($request->phone);
        $email = new Email($request->email);

        if (null !== $this->repository->findByPhoneAndEmail(phone: $phone, email: $email)) {
            throw new InvalidArgumentException('Клиент уже зарегестрирован');
        }

        $client = Client::create(
            firstName: $request->firstName,
            lastName: $request->lastName,
            phone: $phone,
            email: $email,
            education: $request->education,
            personalDataConsent: $request->personalDataConsent,
        );

        $result = $this->scoringService->calculate($client);
        $client->applyScoring($result->total);

        $this->repository->save($client);
        $this->repository->flush();

        return $client;
    }
}
