<?php

namespace App\Entity;

use App\Repository\CustomerEventRepository;
use App\Entity\Common\DatedInterface;
use App\Entity\Common\DatedTrait;
use App\Entity\Common\IdInterface;
use App\Entity\Common\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table("`customer_event`")
 * @ORM\Entity(repositoryClass=CustomerEventRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"customer_events_read"}}
 * )
 */
class CustomerEvent implements DatedInterface, IdInterface
{
    use DatedTrait;
    use IdTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, cascade={"persist"})
     * @Groups({"customer_events_read"})
     */
    private $customer = null;

    /**
     * @ORM\ManyToOne(targetEntity=Prospect::class, cascade={"persist"})
     * @Groups({"customer_events_read"})
     */
    private $prospect = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"customer_events_read"})
     */
    private array $events = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getProspect(): Prospect
    {
        return $this->prospect;
    }

    public function setProspect(Prospect $prospect): self
    {
        $this->prospect = $prospect;

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

    public function getInfos(): array
    {
        return [
            'id' => $this->getId(),
            'customer' => $this->getCustomer(),
            'events' => $this->getEvents(),
            'prospect' => $this->getProspect() ?? null,
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt()
        ];
    }
}
