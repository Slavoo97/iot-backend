<?php

namespace App\Utils;

class MqttConfig
{
    /** @var string */
    private string $host;

    /** @var int */
    private int $port;

    /** @var string */
    private string $clientId;

    /** @var string */
    private string $lightTopic;

    /** @var string */
    private string $humidityTopic;

    /** @var string */
    private string $imageTopic;

    public function __construct($lightTopic, $host, $port, $clientId, $humidityTopic, $imageTopic) {
        $this->lightTopic = $lightTopic;
        $this->humidityTopic = $humidityTopic;
        $this->host = $host;
        $this->port = $port;
        $this->clientId = $clientId;
        $this->imageTopic = $imageTopic;
    }

    public function getLightTopic(): string
    {
        return $this->lightTopic;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getHumidityTopic(): string
    {
        return $this->humidityTopic;
    }

    public function getImageTopic(): string
    {
        return $this->imageTopic;
    }
}