<?php

declare(strict_types=1);

namespace App\Core\Client\Entity;

use App\Core\Client\Enum\Education;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
#[ORM\UniqueConstraint(name: 'uidx_phone', columns: ['phone'])]
#[ORM\UniqueConstraint(name: 'uidx_email', columns: ['email'])]
class Client
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;
    private function __construct(
        #[ORM\Column(type: Types::STRING, length: 100, nullable: false)]
        private string $firstName,
        #[ORM\Column(type: Types::STRING, length: 100, nullable: false)]
        private string $lastName,
        #[ORM\Column(type: Types::STRING, length: 20, nullable: false)]
        private string $phone,
        #[ORM\Column(length: 180)]
        private string $email,
        #[ORM\Column(length: 20, enumType: Education::class)]
        private Education $education,
        #[ORM\Column]
        private bool $personalDataConsent,
        #[ORM\Column]
        private ?DateTimeImmutable $registeredAt = null,
        #[ORM\Column]
        private int $scoring = 0,
        #[ORM\Column(nullable: true)]
        private ?DateTimeImmutable $scoringCalculatedAt = null,
    ) {
        $this->id = Uuid::v4();
        $this->registeredAt = new DateTimeImmutable();
    }

    public static function create(
        string $firstName,
        string $lastName,
        PhoneNumber $phone,
        Email $email,
        Education $education,
        bool $personalDataConsent,
    ): self {
        return new self(
            firstName:trim($firstName),
            lastName: trim($lastName),
            phone: $phone->value(),
            email: $email->value(),
            education: $education,
            personalDataConsent: $personalDataConsent,
        );
    }

    public function changeFirstName(string $firstName): void
    {
        if ($this->getFirstName() !== $firstName) {
            $this->firstName = $firstName;
        }
    }

    public function changeLastName(string $lastName): void
    {
        if ($this->getLastName() !== $lastName) {
            $this->lastName = $lastName;
        }
    }

    public function changePhone(PhoneNumber $phone): void
    {
        if ($this->getPhone()->value() !== $phone->value()) {
            $this->phone = $phone->value();
        }
    }

    public function changeEmail(Email $email): void
    {
        if ($this->email !== $email->value()) {
            $this->email = $email->value();
        }
    }

    public function changeEducation(Education $education): void
    {
        if ($this->education->value !== $education->value) {
            $this->education = $education;
        }
    }

    public function changePersonalDataConsent(bool $personalDataConsent): void
    {
        if ($this->personalDataConsent !== $personalDataConsent) {
            $this->personalDataConsent = $personalDataConsent;
        }
    }

    public function applyScoring(int $scoring): void
    {
        $this->scoring = $scoring;
        $this->scoringCalculatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Нормализованный номер (7XXXXXXXXXX) — для сравнений/скоринга.
     */
    public function getPhone(): PhoneNumber
    {
        return new PhoneNumber($this->phone);
    }

    public function getEmail(): Email
    {
        return new Email($this->email);
    }

    /**
     * Домен email без зоны: gmail.com -> gmail.
     */
    public function getEmailDomainKey(): string
    {
        $host = substr($this->email, strpos($this->email, '@') + 1);

        return mb_strtolower(explode('.', $host)[0]);
    }

    public function getEducation(): Education
    {
        return $this->education;
    }

    public function hasPersonalDataConsent(): bool
    {
        return $this->personalDataConsent;
    }

    public function getScoring(): int
    {
        return $this->scoring;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getScoringCalculatedAt(): ?DateTimeImmutable
    {
        return $this->scoringCalculatedAt;
    }
}
