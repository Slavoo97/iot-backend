<?php

namespace App\Command;

use App\Model\Services\TemperatureRepository;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOldTemperatureCommand extends Command
{
    protected static $defaultName = 'app:delete:old-temperatures';

    /** @var TemperatureRepository */
    private TemperatureRepository $temperatureRepository;

    public function __construct(TemperatureRepository $temperatureRepository)
    {
        parent::__construct();
        $this->temperatureRepository = $temperatureRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete temperature records older than one month')
            ->setHelp('This command deletes all temperature records in the database that are older than one month.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Deleting old temperature records...');

        $thresholdDate = new DateTime('-1 month');
        $deletedCount = $this->temperatureRepository->deleteOlderThan($thresholdDate);

        $io->success("Deleted {$deletedCount} temperature records older than one month.");

        return Command::SUCCESS;
    }
}
