<?php

namespace App\DataFixtures;

use App\Entity\Prospect;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class ProspectFixtures extends Fixture implements DependentFixtureInterface
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
        foreach ($this->getData() as $data) {
            $entity = $this->createProspect($data);
            $manager->persist($entity);
            $commercial = $this->getReference(AccountFixtures::getAccountCommercialReference((string) $i));
            $entity->setCommercial($commercial);
            ++$i;
        }

        $manager->flush();
    }

    private function createProspect(array $data): Prospect
    {
        $entity = new Prospect();

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();

        foreach ($data as $key => $value) {
            if ($propertyAccessor->isWritable($entity, $key)) {
                $propertyAccessor->setValue($entity, $key, $value);
            }
        }

        return $entity;
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;
        $slugger = new AsciiSlugger('fr');

        for ($i = 0; $i < 19; ++$i) {
            $firstname = $faker->firstname();
            $lastname = $faker->lastname();
            $phone = $faker->phoneNumber();

            $email = '';
            $email .= $slugger->slug($firstname);
            if ($faker->boolean(40)) {
                $email .= '.';
            }
            $email .= $slugger->slug($lastname);
            if ($faker->boolean(30)) {
                $email .= $faker->numberBetween(10, 90);
            }
            if ($faker->boolean(40)) {
                $email .= '@' . $faker->domainName();
            } else {
                $email .= '@' . $faker->freeEmailDomain();
            }

            $data = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'phone' => $phone,
            ];
            yield $data;
        }
    }
}
