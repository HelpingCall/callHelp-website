<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
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
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $firstname;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $lastname;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max="255")
     */
    private $email;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank
     * @Assert\Length(max="10")
     */
    private $housenumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $zipcode;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $street;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $city;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $telephonenumber;

    /**
     * @var UuidInterface|null
     * @ORM\Column(type="uuid", unique=true)
     */
    private $userID;

    public function getId(): ?UuidInterface
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getHousenumber(): ?string
    {
        return $this->housenumber;
    }

    public function setHousenumber(string $housenumber): self
    {
        $this->housenumber = $housenumber;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getTelephonenumber(): ?string
    {
        return $this->telephonenumber;
    }

    public function setTelephonenumber(string $telephonenumber): self
    {
        $this->telephonenumber = $telephonenumber;

        return $this;
    }

    public function getUserID(): ?UuidInterface
    {
        return $this->userID;
    }

    public function setUserID(UuidInterface $userID): self
    {
        $this->userID = $userID;

        return $this;
    }
}
