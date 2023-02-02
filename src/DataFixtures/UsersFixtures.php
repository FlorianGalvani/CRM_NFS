<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class UsersFixtures extends Fixture implements FixtureGroupInterface
{
    public const MICHEL_ADMIN = 'michel.admin@nfs.school';
    public const MICHEL_COMMERCIAL = 'michel.commercial@nfs.school';
    public const MICHEL_CUSTOMER = 'michel.customer@nfs.school';

    private $fakerFactory;
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public static function getGroups(): array
    {
        return ['user', 'account'];
    }

    public static function getUserReference(string $key): string
    {
        return User::class . '_' . $key;
    }

    public static function getUserMichelReference(string $email): string
    {
        return User::class . '_Michel_' . $email;
    }

    public static function getUserCustomerMichelReference(string $email): string
    {
        return User::class . '_Customer_Michel_' . $email;
    }

    public function load(ObjectManager $manager): void
    {
        // Michel(s)
        foreach ($this->getMichelData() as $data) {
            $entity = $this->createUser($data);
            $manager->persist($entity);
            $this->addReference(self::getUserMichelReference($entity->getEmail()), $entity);
        }

        // 100 random user(s)
        $i = 0;
        foreach ($this->getData(100) as $data) {
            $entity = $this->createUser($data);
            $manager->persist($entity);
            $this->addReference(self::getUserReference((string) $i), $entity);
            ++$i;
        }

        $x = 100;
        foreach($this->getData(10) as $customerData) {
            $entity = $this->createUser($customerData);
            $manager->persist($entity);
            $this->addReference(self::getUserCustomerMichelReference((string) $x), $entity);
            ++$x;
        }

        $manager->flush();
    }

    public function createUser(array $data): User
    {
        $entity = new User();

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();

        if ($plainPassword = $data['plainPassword'] ?? null) {
            $password = $this->userPasswordHasher->hashPassword($entity, $plainPassword);
            $data['password'] = $password;
            unset($data['plainPassword']);
        }

        foreach ($data as $key => $value) {
            if ($propertyAccessor->isWritable($entity, $key)) {
                $propertyAccessor->setValue($entity, $key, $value);
            }
        }

        return $entity;
    }

    private function getMichelData(): iterable
    {
        yield [
            'email' => self::MICHEL_ADMIN,
            'plainPassword' => self::MICHEL_ADMIN,
            'firstname' => "Michel",
            'lastname' => "Admin",
            'roles' => ['ROLE_ADMIN'],
            'phone' => '0000000000',
            'address' => '10 Rue du Général Sarrail, 76000 Rouen'
        ];
        yield [
            'email' => self::MICHEL_COMMERCIAL,
            'plainPassword' => self::MICHEL_COMMERCIAL,
            'firstname' => "Michel",
            'lastname' => "Commercial",
            'roles' => ['ROLE_USER'],
            'phone' => '0000000000',
            'address' => '10 Rue du Général Sarrail, 76000 Rouen'
        ];
        yield [
            'email' => self::MICHEL_CUSTOMER,
            'plainPassword' => self::MICHEL_CUSTOMER,
            'firstname' => "Michel",
            'lastname' => "Customer",
            'roles' => ['ROLE_USER'],
            'phone' => '0000000000',
            'address' => '10 Rue du Général Sarrail, 76000 Rouen'
        ];
    }

    private function getData($number): iterable
    {
        $faker = $this->fakerFactory;
        $slugger = new AsciiSlugger('fr');

        for ($i = 0; $i < $number; ++$i) {
            $firstname = $faker->firstname();
            $lastname = $faker->lastname();
            $phone = $faker->phoneNumber();
            $address = $faker->address();
            $company = $faker->company();

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
                'password' => $faker->password(),
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'company' => $company,
                'roles' => ['ROLE_USER'],
            ];
            yield $data;
        }
    }
}