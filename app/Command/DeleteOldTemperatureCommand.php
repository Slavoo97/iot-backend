<?php

namespace App\Command;

use App\Model\Services\HumidityRepository;
use App\Model\Services\ImageRepository;
use App\Model\Services\LightStateRepository;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOldTemperatureCommand extends Command
{
    protected static $defaultName = 'app:delete:old-temperatures';

    /** @var LightStateRepository */
    private LightStateRepository $temperatureRepository;

    /** @var HumidityRepository */
    private HumidityRepository $humidityRepository;

    /** @var ImageRepository */
    private ImageRepository $imageRepository;

    public function __construct(HumidityRepository $humidityRepository, LightStateRepository $temperatureRepository, ImageRepository $imageRepository)
    {
        parent::__construct();
        $this->temperatureRepository = $temperatureRepository;
        $this->humidityRepository = $humidityRepository;
        $this->imageRepository = $imageRepository;
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
        $this->humidityRepository->deleteOlderThan($thresholdDate);
        $this->imageRepository->deleteOlderThan($thresholdDate);

        $io->success("Deleted {$deletedCount} temperature records older than one month.");

        return Command::SUCCESS;
    }
}
