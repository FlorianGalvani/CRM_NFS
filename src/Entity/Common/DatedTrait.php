<?php

namespace App\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait DatedTrait
{
    /**
     * @ORM\Column(type="datetime")
     * @Groups({"transaction_read", "user_read", "account_read", "customer_read", "prospect_read", "transaction_read"})
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"transaction_read", "user_read", "account_read", "customer_read", "prospect_read", "transaction_read"})
     */
    protected $updatedAt = null;

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}