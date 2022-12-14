<?php

namespace App\Entity\Common;

interface DatedInterface
{
    public function getCreatedAt(): ?\DateTime;

    public function setCreatedAt(?\DateTime $createdAt): self;

    public function getUpdatedAt(): ?\DateTime;

    public function setUpdatedAt(?\DateTime $updatedAt): self;
}