<?php

namespace App\Command;

use App\Model\Services\HumidityRepository;
use App\Utils\MqttConfig;
use App\Utils\Services\MqttService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MqttSubscribeHumidityCommand extends Command
{
    protected static $defaultName = 'app:subscribe:humidity';

    /** @var MqttService */
    private MqttService $mqttService;

    /** @var MqttConfig */
    private MqttConfig $mqttConfig;

    /** @var HumidityRepository */
    private HumidityRepository $humidityRepository;

    public function __construct(MqttService $mqttService, MqttConfig $mqttConfig, HumidityRepository $humidityRepository)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
        $this->mqttConfig = $mqttConfig;
        $this->humidityRepository = $humidityRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Subscribe to an MQTT humidity and store messages in the database')
            ->setHelp('This command allows you to subscribe to an MQTT topic and save incoming messages to the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting MQTT subscriber...');
        $this->mqttService->connect();

//        $io->text('Subscribing to topic: ' . $this->mqttConfig->getHumidityTopic());

        $topics = [
            [
                'name' => $this->mqttConfig->getHumidityTopic(),
                'callback' => function ($topic, $message) use ($io) {
                    $data = json_decode($message, true);

                    if (isset($data['metrics'][0]['value'])) {
                        $humidityValue = $data['metrics'][0]['value'];
                        $io->text($humidityValue);

                        $this->humidityRepository->create((string)$humidityValue);

                        $io->success("Vlhkosť {$humidityValue} uložená do databázy.");
                    } else {
                        $io->error("Neplatná správa prijatá: {$message}");
                    }
                }
            ]
//            ,
//            [
//                'name' => $this->mqttConfig->getImageTopic(),
//                'callback' => function ($topic, $message) use ($io) {
//                    $data = json_decode($message, true);
//
//                    if (isset($data['metrics'][0]['value'])) {
//                        $humidityValue = $data['metrics'][0]['value'];
//                        $io->text($humidityValue);
//
//                        $this->humidityRepository->create((string)$humidityValue);
//
//                        $io->success("Vlhkosť {$humidityValue} uložená do databázy.");
//                    } else {
//                        $io->error("Neplatná správa prijatá: {$message}");
//                    }
//                }
//            ]
        ];
        $this->mqttService->listen($topics);

        $io->success('Subscriber finished.');
        return Command::SUCCESS;
    }
}
