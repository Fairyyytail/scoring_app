<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Core\Client\Enum\Education;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateClientRequest
{
    public function __construct(
        public ?string $id = null
    ) {
    }

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 18)]
    public ?string $phone = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public Education $education;

    #[Assert\Type('boolean')]
    public bool $personalDataConsent = false;
}
