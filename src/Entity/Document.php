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
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"document_read"}}
 * )
 */
class Document implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    /**
     * @ORM\ManyToOne
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"document_read"})
     */
    private ?Account $account = null;

    /**
     * @ORM\ManyToOne(inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"document_read"})
     */
    private ?Transaction $transaction = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"document_read"})
     */
    private ?string $type = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"document_read"})
     */
    private ?string $fileName = null;

    /**
     * @ORM\Column(length=255)
     * @Groups({"document_read"})
     */
    private ?string $fileExtension = null;

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

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
}
