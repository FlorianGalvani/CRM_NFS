<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DatedTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected ?\DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $updatedAt = null;

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}