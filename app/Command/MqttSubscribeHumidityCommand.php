<?php

namespace App\Command;

use App\Model\Entity\LightState;
use App\Model\Services\HumidityRepository;
use App\Model\Services\ImageRepository;
use App\Model\Services\LightStateRepository;
use App\Utils\MqttConfig;
use App\Utils\Services\MqttService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
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

    /** @var ImageRepository */
    private ImageRepository $imageRepository;

    /** @var LightStateRepository */
    private LightStateRepository $lightStateRepository;

    public function __construct(MqttService $mqttService, MqttConfig $mqttConfig, HumidityRepository $humidityRepository, ImageRepository $imageRepository, LightStateRepository $lightStateRepository)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
        $this->mqttConfig = $mqttConfig;
        $this->humidityRepository = $humidityRepository;
        $this->imageRepository = $imageRepository;
        $this->lightStateRepository = $lightStateRepository;
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

                        $io->success("Humidity {$humidityValue} saved into db.");
                    } else {
                        $io->error("Wrong message: {$message}");
                    }
                }
            ],
            [
                'name' => $this->mqttConfig->getImageTopic(),
                'callback' => function ($topic, $message) use ($io) {
                    $data = json_decode($message, true);

                    if (isset($data['image_data']) && isset($data['image_name'])) {
                        $imageData = $data['image_data'];
                        $fileName = $data['image_name'];
                        $io->text("Received image: {$fileName}");
//                        $io->text("Image data: {$imageData}");

                        $imageData = base64_decode($imageData);
                        $directory = __DIR__ . '/../../www/upload/images/';
                        $filePath = $directory . $fileName;

                        if (!is_dir($directory)) {
                            mkdir($directory, 0777, true);
                        }

                        file_put_contents($filePath, $imageData);

                        $this->imageRepository->createImage('/upload/images/' . $fileName, $fileName);

                    } else {
                        $io->error("Wrong message: {$message}");
                    }
                }
            ],
            [
                'name' => $this->mqttConfig->getLightTopic(),
                'callback' => function ($topic, $message) use ($io) {
                    $data = json_decode($message, true);

                    if (isset($data['status'])) {
                        $status = $data['status'];
                        $io->text("Light toggled: {$status}");

                        $statusState = null;
                        if ($status === 'on') {
                            $statusState = LightState::STATE_ON;
                        } elseif ($status === 'off') {
                            $statusState = LightState::STATE_OFF;
                        } else {
                            $io->error("Wrong light status: {$status}");
                        }

                        $io->text("raw status: {$status}");
                        $io->text("status: {$statusState}");

                        if (in_array($status, [LightState::STATE_ON, LightState::STATE_OFF])) {
                            if ($this->lightStateRepository->findOneLightStateBy([], ['id' => "DESC"])->getState() !== $statusState) {
                                $this->lightStateRepository->create($statusState);
                            } else {
                                $io->error("Light is already {$status}.");
                            }
                        } else {
                            $io->error("status is null.");
                        }

                    } else {
                        $io->error("Wrong message: {$message}");
                    }
                }
            ]
        ];
        $this->mqttService->listen($topics);

        $io->success('Subscriber finished.');
        return Command::SUCCESS;
    }
}
