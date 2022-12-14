<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\AccountRepository;
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

    public const ACCOUNT_STATUS_PENDING = 'pending';
    public const ACCOUNT_STATUS_ACTIVE = 'active';
    public const ACCOUNT_STATUS_DISABLED = 'disabled';
    public const ACCOUNT_STATUS_DELETED = 'deleted';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"account_read"})
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="account")
     * @Groups({"account_read"})
     */
    private ?User $user;

    /**
     * @ORM\Column(length=255)
     * @Groups({"account_read"})
     */
    private ?string $type = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"account_read"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"account_read"})
     */
    private ?string $accountStatus = null;

    /**
     * @ORM\OneToMany(mappedBy="commercial", targetEntity=Prospect::class, orphanRemoval=true)
     * @Groups({"account_read"})
     */
    private Collection $prospects;

    /**
     * @ORM\OneToMany(mappedBy="commercial", targetEntity=Account::class, orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups({"account_read"})
     */
    private Collection $customers;

    /**
     * @ORM\ManyToOne(inversedBy="customers", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"account_read"})
     */
    private ?Account $commercial = null;

    public function __construct()
    {
        $this->prospects = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
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

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAccountStatus(): ?string
    {
        return $this->accountStatus;
    }

    public function setAccountStatus(string $accountStatus): self
    {
        $this->accountStatus = $accountStatus;

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

    /**
     * @return Collection
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Account $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCommercial($this);
        }

        return $this;
    }

    public function removeCustomer(Account $customer): self
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCommercial() === $this) {
                $customer->setCommercial(null);
            }
        }

        return $this;
    }

    /**
     * @return Account|null
     */
    public function getCommercial(): ?Account
    {
        return $this->commercial;
    }

    /**
     * @param Account|null $commercial
     */
    public function setCommercial(?Account $commercial): void
    {
        $this->commercial = $commercial;
    }

}
