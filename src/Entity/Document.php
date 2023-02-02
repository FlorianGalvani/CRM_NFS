<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table("`document`")
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"document_read"}}
 * )
 */
class Document implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    const TRANSACTION_DOCUMENT_QUOTATION = 'devis';
    const TRANSACTION_DOCUMENT_INVOICE = 'facture';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"document_read"})
     */
    private $customer = null;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"document_read"})
     */
    private $commercial = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Transaction", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"document_read"})
     */
    private $transaction = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"document_read"})
     */
    private $type = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"document_read"})
     */
    private $fileName = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"document_read"})
     */
    private $fileExtension = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"document_read"})
     */
    private $data = null;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function getCustomer(): ?Account
    {
        return $this->customer;
    }

    public function setCustomer(?Account $account): self
    {
        $this->customer = $account;

        return $this;
    }

    public function getCommercial(): ?Account
    {
        return $this->commercial;
    }

    public function setCommercial(?Account $account): self
    {
        $this->commercial = $account;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getInfos(): array
    {
        return [
            'id' => $this->getId(),
            'customer' => $this->getCustomer(),
            'type' => $this->getType(),
            'data' => json_decode($this->getData()),
            'fileName' => $this->getFileName(),
            'fileExtension' => $this->getFileExtension(),
            'transaction' => $this->getTransaction(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];

    }

}
