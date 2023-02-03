<?php

namespace App\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait IdTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"transaction_read", "user_read", "account_read", "customer_read", "prospect_read", "transaction_read"})
     */
    private $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

}