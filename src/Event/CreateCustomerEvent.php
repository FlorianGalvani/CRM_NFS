<?php

namespace App\Event;

use App\Entity\Account;

class CreateCustomerEvent
{
    public const NAME = 'customer.create';
    private $customer;

    public function __construct(Account $customer)
    {
        $this->customer = $customer;
    }

    public function getCustomer()
    {
        return $this->customer;
    }
}