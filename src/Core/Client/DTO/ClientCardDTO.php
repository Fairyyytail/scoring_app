<?php

declare(strict_types=1);

namespace App\Core\Client\DTO;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use DateTimeImmutable;

final readonly class ClientCardDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public string $phoneNumber,
        public string $email,
        public Education $education,
        public bool $personalDataConsent,
        public string $scoring,
        public ?DateTimeImmutable $scoringCalculatedAt
    ) {
    }

    public static function fromEntity(Client $client): self
    {
        return new self(
            id: (string) $client->getId(),
            firstName: $client->getFirstName(),
            lastName: $client->getLastName(),
            fullName: $client->getFullName(),
            phoneNumber: $client->getPhone()->formatted(),
            email: $client->getEmail()->value(),
            education: $client->getEducation(),
            personalDataConsent: $client->hasPersonalDataConsent(),
            scoring: (string) $client->getScoring(),
            scoringCalculatedAt: $client->getScoringCalculatedAt(),
        );
    }
}
