<?php

declare(strict_types=1);

namespace App\Presentation\Command;

use App\Core\Client\ClientRepositoryInterface;
use App\Core\Client\Service\ScoringService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:scoring:calculate',
    description: 'Рассчитывает скоринг по всем клиентам или по одному клиенту',
)]
final class CalculateScoringCommand extends Command
{
    public function __construct(
        private readonly ClientRepositoryInterface $repository,
        private readonly ScoringService $scoringService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'clientId',
            InputArgument::OPTIONAL,
            'ID клиента. Если не указан — скоринг пересчитывается для всех клиентов.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $clientId = $input->getArgument('clientId');

        $clients = null !== $clientId
            ? [$this->repository->getById(Uuid::fromString($clientId))]
            : $this->repository->findAll();

        if ([] === $clients) {
            $io->warning('Клиенты не найдены.');

            return Command::SUCCESS;
        }
        $counter = 0;
        foreach ($clients as $client) {
            $counter++;

            $result = $this->scoringService->calculate($client);
            $client->applyScoring($result->total);
            $this->repository->save($client);

            $io->section(sprintf(
                '%s (%s) — итоговый скоринг: %d',
                $client->getFullName(),
                $client->getId(),
                $result->total,
            ));

            $table = new Table($output);
            $table->setHeaders(['Правило', 'Баллы']);
            foreach ($result->details as $detail) {
                $table->addRow([$detail->reason, $detail->points]);
            }
            $table->render();
        }

        $io->success(sprintf('Скоринг рассчитан для %d клиент(а/ов).', $counter));

        return Command::SUCCESS;
    }
}
