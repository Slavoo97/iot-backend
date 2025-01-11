<?php
/**
 * This file is part of the iot-backend project.
 * Copyright (c) 2025 Slavomír Švigar <slavo.svigar@gmail.com>
 */

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Services\TemperatureRepository")
 * @ORM\Table(name="temperature")
 * @package App\Model\Entity
 */
class Temperature
{

    /**
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;

    /**
     *
     * @ORM\Column(type="string", nullable = false)
     * @var string
     */
    private $temperature;

    /**
     *
     * @ORM\Column(type="datetime", nullable = true)
     * @var \DateTime
     */
    private $date;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTemperature(): string
    {
        return $this->temperature;
    }

    /**
     * @param string $temperature
     */
    public function setTemperature(string $temperature): void
    {
        $this->temperature = $temperature;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }
}
