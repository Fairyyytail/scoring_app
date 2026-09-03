<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Core\Client\Entity\Client;
use App\Core\Client\Enum\Education;
use App\Core\Client\Service\ScoringService;
use App\Core\Client\ValueObject\Email;
use App\Core\Client\ValueObject\PhoneNumber;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ClientFixture extends Fixture
{
    // Имена ссылок (для ReferenceRepository в интеграционных тестах)
    public const string REF_MEGAFON_GMAIL_HIGHER_CONSENT = 'client-megafon-gmail-higher-consent';
    public const string REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT = 'client-beeline-yandex-vocational-no-consent';
    public const string REF_MTS_MAIL_SECONDARY_CONSENT = 'client-mts-mail-secondary-consent';
    public const string REF_OTHER_OPERATOR_OTHER_DOMAIN_NO_CONSENT = 'client-other-other-secondary-no-consent';
    public const string REF_MEGAFON_YANDEX_VOCATIONAL_CONSENT = 'client-megafon-yandex-vocational-consent';
    public const string REF_BEELINE_GMAIL_HIGHER_NO_CONSENT = 'client-beeline-gmail-higher-no-consent';

    /**

     * @var array<string, array{0: string, 1: string, 2: string, 3: string, 4: Education, 5: bool}>
     */
    public const array FIXED_CLIENTS = [
        self::REF_MEGAFON_GMAIL_HIGHER_CONSENT               => ['Иван', 'Иванов', '79261234567', 'ivan@gmail.com',    Education::HIGHER,     true],  // 10+10+15+4=39
        self::REF_BEELINE_YANDEX_VOCATIONAL_NO_CONSENT       => ['Пётр', 'Петров', '79031234567', 'petr@yandex.ru',    Education::VOCATIONAL, false], // 5+8+10+0=23
        self::REF_MTS_MAIL_SECONDARY_CONSENT                 => ['Мария', 'Сидорова', '79101234567', 'maria@mail.ru',  Education::SECONDARY,  true],  // 3+6+5+4=18
        self::REF_OTHER_OPERATOR_OTHER_DOMAIN_NO_CONSENT     => ['Анна', 'Кузнецова', '79001234567', 'anna@corp.io',   Education::SECONDARY,  false], // 1+3+5+0=9
        self::REF_MEGAFON_YANDEX_VOCATIONAL_CONSENT          => ['Сергей', 'Смирнов', '79261234568', 'sergey@yandex.ru', Education::VOCATIONAL, true], // 10+8+10+4=32
        self::REF_BEELINE_GMAIL_HIGHER_NO_CONSENT            => ['Ольга', 'Попова', '79031234568', 'olga@gmail.com',   Education::HIGHER,     false], // 5+10+15+0=30
    ];

    private const int RANDOM_CLIENTS_COUNT = 100;
    private const array RANDOM_EMAIL_DOMAINS = ['gmail.com', 'yandex.ru', 'mail.ru', 'rambler.ru', 'inbox.ru'];
    private const array RANDOM_PHONE_PREFIXES = ['926', '916', '903', '999', '925', '915'];

    public function __construct(
        private readonly ScoringService $scoringService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::FIXED_CLIENTS as $reference => [$firstName, $lastName, $phone, $email, $education, $consent]) {
            $client = $this->createClient($firstName, $lastName, $phone, $email, $education, $consent);

            $manager->persist($client);
            $this->addReference($reference, $client);
        }

        $faker = Factory::create('ru_RU');
        $faker->unique(true);

        for ($i = 0; $i < self::RANDOM_CLIENTS_COUNT; $i++) {
            $phone = '7'.self::RANDOM_PHONE_PREFIXES[array_rand(self::RANDOM_PHONE_PREFIXES)].$faker->unique()->numerify('#######');
            $email = $faker->unique()->userName().'@'.self::RANDOM_EMAIL_DOMAINS[array_rand(self::RANDOM_EMAIL_DOMAINS)];
            $education = Education::cases()[array_rand(Education::cases())];

            $manager->persist($this->createClient(
                $faker->firstName(),
                $faker->lastName(),
                $phone,
                $email,
                $education,
                $faker->boolean(70),
            ));
        }
        $manager->flush();
    }

    private function createClient(
        string $firstName,
        string $lastName,
        string $phone,
        string $email,
        Education $education,
        bool $consent,
    ): Client {
        $client = Client::create(
            firstName: $firstName,
            lastName: $lastName,
            phone: new PhoneNumber($phone),
            email: new Email($email),
            education: $education,
            personalDataConsent: $consent,
        );

        $result = $this->scoringService->calculate($client);
        $client->applyScoring($result->total);

        return $client;
    }
}
