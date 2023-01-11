<?php

namespace App\Entity;

use App\Repository\ProspectRepository;
use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table("`prospect`")
 * @ORM\Entity(repositoryClass=ProspectRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"prospect_read"}}
 * )
 */
class Prospect implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    /**
     * @ORM\ManyToOne(inversedBy="prospects", targetEntity=Account::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"prospect_read"})
     */
    private $commercial = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private $firstname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private $lastname = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"prospect_read"})
     */
    private $email = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"prospect_read"})
     */
    private $phone = null;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

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
