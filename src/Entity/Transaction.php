<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
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
class Transaction implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    public const TRANSACTION_QUOTATION_REQUESTED = 'quotation_requested';
    public const TRANSACTION_QUOTATION_SENT = 'quotation_sent';
    public const TRANSACTION_STATUS_PAYMENT_INTENT = 'payment_intent';
    public const TRANSACTION_STATUS_PAYMENT_SUCCESS = 'payment_success';
    public const TRANSACTION_STATUS_PAYMENT_FAILURE = 'payment_failure';
    public const TRANSACTION_STATUS_PAYMENT_ABANDONED = 'payment_abandoned';

    /**
     * @ORM\ManyToOne
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"transaction_read"})
     */
    private $customer = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"transaction_read"})
     */
    private $label = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"transaction_read"})
     */
    private $type = null;

    /**
     * @ORM\Column
     * @Groups({"transaction_read"})
     */
    private $amount = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"transaction_read"})
     */
    private $stripePaymentIntentId = null;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Groups({"transaction_read"})
     */
    private $paymentStatus = null;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"transaction_read"})
     */
    private $transactionQuotation;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"transaction_read"})
     */
    private $transactionInvoice;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
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
     * @return Document|null
     */
    public function getTransactionQuotation(): ?Document
    {
        return $this->transactionQuotation;
    }

    /**
     * @param Document|null $transactionQuotation
     */
    public function setTransactionQuotation(?Document $transactionQuotation): void
    {
        $this->transactionQuotation = $transactionQuotation;
    }

    /**
     * @return Document|null
     */
    public function getTransactionInvoice(): ?Document
    {
        return $this->transactionInvoice;
    }

    /**
     * @param Document|null $transactionInvoice
     */
    public function setTransactionInvoice(?Document $transactionInvoice): void
    {
        $this->transactionInvoice = $transactionInvoice;
    }

}
