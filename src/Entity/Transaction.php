<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"transaction_read"}}
 * )
 */
class Transaction implements DatedInterface
{
    use DatedTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"transaction_read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"transaction_read"})
     */
    private ?Account $customer = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"transaction_read"})
     */
    private ?string $type = null;

    /**
     * @ORM\Column
     * @Groups({"transaction_read"})
     */
    private ?float $amount = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"transaction_read"})
     */
    private ?string $stripePaymentIntentId = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"transaction_read"})
     */
    private ?string $paymentStatus = null;

    /**
     * @ORM\OneToMany(mappedBy="transaction", targetEntity=Document::class, orphanRemoval=true)
     * @Groups({"transaction_read"})
     */
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Account
    {
        return $this->customer;
    }

    public function setCustomer(?Account $customer): self
    {
        $this->customer = $customer;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): self
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setTransaction($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getTransaction() === $this) {
                $document->setTransaction(null);
            }
        }

        return $this;
    }
}
