<?php
/**
 * This file is part of the iot-backend project.
 * Copyright (c) 2025 Slavomír Švigar <slavo.svigar@gmail.com>
 */

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Services\ImageRepository")
 * @ORM\Table(name="image")
 * @package App\Model\Entity
 */
class Image
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $full;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $thumb;

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
    public function getFull(): string
    {
        return $this->full;
    }

    /**
     * @param string $full
     */
    public function setFull(string $full): void
    {
        $this->full = $full;
    }

    /**
     * @return string
     */
    public function getThumb(): string
    {
        return $this->thumb;
    }

    /**
     * @param string $thumb
     */
    public function setThumb(string $thumb): void
    {
        $this->thumb = $thumb;
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
