<?php

namespace App\Event;

use App\Entity\Prospect;

class CreateProspectEvent
{
    public const NAME = 'prospect.create';
    private $prospect;
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    public function getProspect() {
        return $this->prospect;
    }

}