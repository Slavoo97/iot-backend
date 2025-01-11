<?php

namespace App\Command;

use App\Model\Services\TemperatureRepository;
use App\Utils\MqttConfig;
use App\Utils\Services\MqttService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MqttSubscribeTemperatureCommand extends Command
{
    protected static $defaultName = 'app:subscribe:temperature';

    /** @var MqttService */
    private MqttService $mqttService;

    /** @var MqttConfig */
    private MqttConfig $mqttConfig;

    /** @var TemperatureRepository */
    private TemperatureRepository $temperatureRepository;

    public function __construct(MqttService $mqttService, MqttConfig $mqttConfig, TemperatureRepository $temperatureRepository)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
        $this->mqttConfig = $mqttConfig;
        $this->temperatureRepository = $temperatureRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Subscribe to an MQTT temperature and store messages in the database')
            ->setHelp('This command allows you to subscribe to an MQTT topic and save incoming messages to the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting MQTT subscriber...');
        $this->mqttService->connect();

        $io->text('Subscribing to topic: ' . $this->mqttConfig->getTemperatureTopic());

        $this->mqttService->listen($this->mqttConfig->getTemperatureTopic(), function ($topic, $message) use ($io) {
            $data = json_decode($message, true);

            if (isset($data['value'])) {
                $temperatureValue = $data['value'];
                $io->text($temperatureValue);

                $this->temperatureRepository->create($temperatureValue);

                $io->success("Teplota {$temperatureValue} uložená do databázy.");
            } else {
                $io->error("Neplatná správa prijatá: {$message}");
            }
        });

        $io->success('Subscriber finished.');
        return Command::SUCCESS;
    }
}
