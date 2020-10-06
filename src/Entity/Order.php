<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="integer")
     */
    private $Housenumber;

    /**
     * @ORM\Column(type="integer")
     */
    private $Postalcode;

    /**
     * @ORM\Column(type="array")
     */
    private $orders = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHousenumber(): ?string
    {
        return $this->Housenumber;
    }

    public function setHousenumber(string $Housenumber): self
    {
        $this->Housenumber = $Housenumber;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->Postalcode;
    }

    public function setPostalcode(string $Postalcode): self
    {
        $this->Postalcode = $Postalcode;

        return $this;
    }

    public function getOrders(): ?array
    {
        return $this->orders;
    }

    public function setOrders(array $orders): self
    {
        $this->orders = $orders;

        return $this;
    }
}
