<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"account_read"}}
 * )
 */
class Account implements DatedInterface
{
    use DatedTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"account_read"})
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(inversedBy="type", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"account_read"})
     */
    private ?User $user = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"account_read"})
     */
    private ?string $type = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"account_read"})
     */
    private ?string $registrationStatus = null;

    /**
     * @ORM\OneToMany(mappedBy="commercial", targetEntity=Prospect::class, orphanRemoval=true)
     * @Groups({"account_read"})
     */
    private Collection $prospects;

    public function __construct()
    {
        $this->prospects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRegistrationStatus(): ?string
    {
        return $this->registrationStatus;
    }

    public function setRegistrationStatus(string $registrationStatus): self
    {
        $this->registrationStatus = $registrationStatus;

        return $this;
    }

    /**
     * @return Collection<int, Prospect>
     */
    public function getProspects(): Collection
    {
        return $this->prospects;
    }

    public function addProspect(Prospect $prospect): self
    {
        if (!$this->prospects->contains($prospect)) {
            $this->prospects->add($prospect);
            $prospect->setCommercial($this);
        }

        return $this;
    }

    public function removeProspect(Prospect $prospect): self
    {
        if ($this->prospects->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getCommercial() === $this) {
                $prospect->setCommercial(null);
            }
        }

        return $this;
    }
}
