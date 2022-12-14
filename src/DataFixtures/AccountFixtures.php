<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\User;
use App\Enum\Account\AccountType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class AccountFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $fakerFactory;

    public function __construct()
    {
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
    }

    public static function getGroups(): array
    {
        return ['account'];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getAccountReference(string $key): string
    {
        return Account::class . '_' . $key;
    }

    public static function getAccountMichelReference(string $key): string
    {
        return Account::class . '_MICHEL_' . $key;
    }

    public static function getAccountCommercialReference(string $key): string
    {
        return Account::class . '_COMMERCIAL_' . $key;
    }

    public static function getAccountCustomerReference(string $key): string
    {
        return Account::class . '_CUSTOMER_' . $key;
    }

    public static function getAccountAdminReference(string $key): string
    {
        return Account::class . '_ADMIN_' . $key;
    }
    public function load(ObjectManager $manager): void
    {
        // Michel(s)
        foreach ($this->getMichelData() as $data) {
            $entity = $this->createAccount($data);
            $manager->persist($entity);
            /** @var User $user */
            $user = $this->getReference(UserFixtures::getUserMichelReference($data['user_id']));
            $user->setAccount($entity);
//            $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
            $this->addReference(self::getAccountMichelReference($user->getEmail()), $entity);
        }

        // 100
        $i = 0;
        $iCommercial = 0;
        $iIndividual = 0;
        foreach ($this->getData() as $data) {
            $entity = $this->createAccount($data);
            $manager->persist($entity);
            /** @var User $user */
            $user = $this->getReference(UserFixtures::getUserReference((string) $i));
            $user->setAccount($entity);
            $this->addReference(self::getAccountReference((string) $i), $entity);
            switch ($entity->getType()) {
                case AccountType::COMMERCIAL:
                    $this->addReference(self::getAccountCommercialReference((string) $iCommercial), $entity);
                    ++$iCommercial;
                    break;
                case AccountType::CUSTOMER:
//                    $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $this->addReference(self::getAccountCustomerReference((string) $iIndividual), $entity);
                    ++$iIndividual;
                    break;
                case AccountType::ADMIN:
//                    $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
                    $this->addReference(self::getAccountAdminReference((string) $iIndividual), $entity);
                    ++$iIndividual;
                    break;
            }
            ++$i;
        }

        $manager->flush();
    }

    private function createAccount(array $data): Account
    {
        $entity = new Account();
        // Default
        $entity->setAccountStatus(Account::ACCOUNT_STATUS_ACTIVE);
        // Data
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

    private function getMichelData(): iterable
    {
        yield [
            'user_id' => UserFixtures::MICHEL_ADMIN,
            'type' => AccountType::ADMIN,
            'createdAt' => new \DateTime('2019-03-21'),
        ];
        yield [
            'user_id' => UserFixtures::MICHEL_COMMERCIAL,
            'type' => AccountType::COMMERCIAL,
            'createdAt' => new \DateTime('2019-03-21'),
        ];
        yield [
            'user_id' => UserFixtures::MICHEL_CUSTOMER,
            'type' => AccountType::CUSTOMER,
            'createdAt' => new \DateTime('2019-03-21'),
        ];
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;

        for ($i = 0; $i < 100; ++$i) {
            switch($i % 5) {
                case 0:
                    yield $this->getAdminData($faker, $i);
                    break;
                case 3:
                    yield $this->getCommercialData($faker, $i);
                    break;
                case 1 | 2 | 4:
                    yield $this->getCustomerData($faker, $i);
            }
        }
    }

    private function getCommercialData(Generator $faker, int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::COMMERCIAL,
        ];

        return $data;
    }

    private function getCustomerData(Generator $faker, int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::CUSTOMER,
        ];

        return $data;
    }

    private function getAdminData(Generator $faker, int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::ADMIN,
        ];

        return $data;
    }
}