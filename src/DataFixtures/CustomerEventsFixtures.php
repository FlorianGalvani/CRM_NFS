<?php

namespace App\DataFixtures;

use App\Entity\CustomerEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CustomerEventsFixtures extends Fixture implements DependentFixtureInterface
{
    private $fakerFactory;

    public function __construct()
    {
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach($this->getData() as $data) {
            $entity = new CustomerEvent();

            $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->disableExceptionOnInvalidPropertyPath()
                ->getPropertyAccessor();

            foreach ($data as $key => $value) {
                if ($propertyAccessor->isWritable($entity, $key)) {
                    $propertyAccessor->setValue($entity, $key, $value);
                }
            }

            $manager->persist($entity);
            ++$i;
        }

        $manager->flush();
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;
        $randomDate = $faker->dateTimeBetween('+60 days', '+90 days');

        for($i = 0; $i < 80; $i++) {
            $data = [
                'customer' => $this->getReference(AccountFixtures::getAccountCustomerReference((string) $faker->numberBetween(0, 78))),
                'events' => [
                    'prospect_created' => new \DateTime(),
                    'conversion_to_customer' => $randomDate,
                    'phone_call' => $randomDate,
                    'meeting' => $randomDate,
                    'quotation_requested' => $randomDate,
                    'invoice_sent' => $randomDate,
                    'invoice_paid' => $randomDate,
                    'payment_success' => $randomDate
                ]
            ];
            yield $data;
        }
    }
}
