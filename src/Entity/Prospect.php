<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ProspectRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"prospect_read"}}
 * )
 */
class Prospect implements DatedInterface
{
    use DatedTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"prospect_read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(inversedBy="propsects")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"prospect_read"})
     */
    private ?Account $commercial = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private ?string $firstname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private ?string $lastname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private ?string $email = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"prospect_read"})
     */
    private ?string $phone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercial(): ?Account
    {
        return $this->commercial;
    }

    public function setCommercial(?Account $commercial): self
    {
        $this->commercial = $commercial;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
