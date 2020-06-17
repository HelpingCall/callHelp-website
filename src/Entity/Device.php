<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass=DeviceRepository::class)
 */
class Device
{
    /**
     * @var UuidInterface|null
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastLat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastLong;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $batteryState;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="device")
     */
    private $user;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getLastLat(): ?string
    {
        return $this->lastLat;
    }

    public function setLastLat(?string $lastLat): self
    {
        $this->lastLat = $lastLat;

        return $this;
    }

    public function getLastLong(): ?string
    {
        return $this->lastLong;
    }

    public function setLastLong(?string $lastLong): self
    {
        $this->lastLong = $lastLong;

        return $this;
    }

    public function getBatteryState(): ?string
    {
        return $this->batteryState;
    }

    public function setBatteryState(?string $batteryState): self
    {
        $this->batteryState = $batteryState;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
