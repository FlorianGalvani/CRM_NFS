<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CustomerEventRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"customer_events_read"}}
 * )
 */
class CustomerEvent implements DatedInterface
{
    use DatedTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"customer_events_read"})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @Groups({"customer_events_read"})
     */
    private Account $customer;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @Groups({"customer_events_read"})
     */
    private Account $commercial;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"customer_events_read"})
     */
    private array $events = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): Account
    {
        return $this->customer;
    }

    public function setCustomer(Account $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
    public function getCommercial(): Account
    {
        return $this->commercial;
    }

    public function setCommercial(Account $commercial): self
    {
        $this->commercial = $commercial;

        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setEvents(?array $events): self
    {
        $this->events = $events;

        return $this;
    }
}
