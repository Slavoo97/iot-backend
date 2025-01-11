<?php

namespace App\Utils\Services;

use App\Utils\MqttConfig;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\ProtocolNotSupportedException;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;

class MqttService
{
    /** @var MqttClient */
    private MqttClient $client;

    /** @var MqttConfig */
    public MqttConfig $mqttConfig;

    /** @var ConnectionSettings */
    public ConnectionSettings $connectionSettings;

    /**
     * @throws ProtocolNotSupportedException
     */
    public function __construct(MqttConfig $mqttConfig)
    {

        $this->mqttConfig = $mqttConfig;
        $this->client = new MqttClient(
            $this->mqttConfig->getHost(),
            $this->mqttConfig->getPort(),
            $this->mqttConfig->getClientId()
        );

        $this->connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUsername("maker")
            ->setPassword("mother.mqtt.password");
    }

    /**
     * Pripojenie k MQTT brokeru
     */
    public function connect(): void
    {
        try {
            echo $this->mqttConfig->getHost();
            $this->client->connect($this->connectionSettings, true);
            echo "Pripojený k MQTT brokeru na {$this->client->getHost()}:{$this->client->getPort()}\n";
        } catch (MqttClientException $e) {
            echo "Chyba pripojenia k MQTT brokeru: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Počúvanie na špecifickej MQTT téme
     *
     * @param string $topic MQTT téma
     */
    public function listen(string $topic, callable $onMessageReceived): void
    {
        try {

            $this->client->subscribe($topic, function ($topic, $message) use ($onMessageReceived) {
                // Zavoláme callback funkciu s prijatou správou
                $onMessageReceived($topic, $message);
            }, 0);

            $this->client->loop(true);
        } catch (MqttClientException $e) {
            echo "Chyba pri počúvaní na MQTT téme: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Odpojenie od MQTT brokeru
     */
    public function disconnect(): void
    {
        try {
            $this->client->disconnect();
            echo "Odpojený od MQTT brokeru.\n";
        } catch (MqttClientException $e) {
            echo "Chyba pri odpojení od MQTT brokeru: " . $e->getMessage() . "\n";
        }
    }
}
