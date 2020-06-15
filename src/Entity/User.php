<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @var UuidInterface|null
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Helper::class, mappedBy="userid")
     */
    private $helpers;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $jwt;

    /**
     * @ORM\OneToMany(targetEntity=Medicals::class, mappedBy="user", orphanRemoval=true)
     */
    private $medicals;

    /**
     * @ORM\OneToMany(targetEntity=Device::class, mappedBy="user")
     */
    private $device;

    public function __construct()
    {
        $this->helpers = new ArrayCollection();
        $this->medicals = new ArrayCollection();
        $this->device = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Helper[]
     */
    public function getHelpers(): Collection
    {
        return $this->helpers;
    }

    public function addHelper(Helper $helper): self
    {
        if (!$this->helpers->contains($helper)) {
            $this->helpers[] = $helper;
            $helper->setUserid($this);
        }

        return $this;
    }

    public function removeHelper(Helper $helper): self
    {
        if ($this->helpers->contains($helper)) {
            $this->helpers->removeElement($helper);
            // set the owning side to null (unless already changed)
            if ($helper->getUserid() === $this) {
                $helper->setUserid(null);
            }
        }
    }

    public function getJwt(): ?string
    {
        return $this->jwt;
    }

    public function setJwt(string $jwt): self
    {
        $this->jwt = $jwt;

        return $this;
    }

    /**
     * @return Collection|Medicals[]
     */
    public function getMedicals(): Collection
    {
        return $this->medicals;
    }

    public function addMedical(Medicals $medical): self
    {
        if (!$this->medicals->contains($medical)) {
            $this->medicals[] = $medical;
            $medical->setUser($this);
        }

        return $this;
    }

    public function removeMedical(Medicals $medical): self
    {
        if ($this->medicals->contains($medical)) {
            $this->medicals->removeElement($medical);
            // set the owning side to null (unless already changed)
            if ($medical->getUser() === $this) {
                $medical->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Device[]
     */
    public function getDevice(): Collection
    {
        return $this->device;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->device->contains($device)) {
            $this->device[] = $device;
            $device->setUser($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->device->contains($device)) {
            $this->device->removeElement($device);
            // set the owning side to null (unless already changed)
            if ($device->getUser() === $this) {
                $device->setUser(null);
            }
        }

        return $this;
    }
}
