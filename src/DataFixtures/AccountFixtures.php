<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\CustomerEvent;
use App\Entity\User;
use App\Enum\Account\AccountType;
use App\Enum\Customer\EventType;
use App\Event\CreateCustomerEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Faker;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class AccountFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $prospectFixtures;
    private $userFixtures;
    private $fakerFactory;
    private $eventDispatcher;

    public function __construct(
        ProspectFixtures $prospectFixtures,
        EventDispatcherInterface $eventDispatcher,
        UsersFixtures $userFixtures
    )
    {
        $this->prospectFixtures = $prospectFixtures;
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
        $this->eventDispatcher = $eventDispatcher;
        $this->userFixtures = $userFixtures;
    }

    public static function getGroups(): array
    {
        return ['account'];
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
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

    public static function getAccountMichelCustomerReference(string $key): string
    {
        return Account::class . '_MICHEL_CUSTOMER_' . $key;
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
        $faker = Faker\Factory::create();
        $accounts = [];
        // Michel(s)
        foreach ($this->getMichelData() as $data) {
            $entity = $this->createAccount($data);
            if($entity->getType() === AccountType::COMMERCIAL) {
                $entity->setData(json_encode([
                    "name" => "NFS",
                    "address" => "1 rue de la paix",
                    "zipCode" => "76000",
                    "city" => "Rouen",
                    "country" => "France",
                    "company" => [
                        "name" => "NFS",
                        "address" => "1 rue de la paix",
                        "zipCode" => "76000",
                        "city" => "Rouen",
                        "country" => "France",
                    ]
                ]));
            }
            if ($entity->getType() === AccountType::CUSTOMER) {
                $entity->setData(json_encode([
                    "address" => "14 rue du bonheur",
                    "zipCode" => "76000",
                    "city" => "Rouen",
                    "country" => "France",
                ]));
            }
            $manager->persist($entity);
            /** @var User $user */
            $user = $this->getReference(UsersFixtures::getUserMichelReference($data['user_id']));
            $user->setAccount($entity);
            $this->addReference(self::getAccountMichelReference($user->getEmail()), $entity);

            if($entity->getType() === AccountType::CUSTOMER) {

                foreach($accounts as $account) {
                    if($account->getType() === AccountType::COMMERCIAL) {
                        $account->addCustomer($entity);
                    }
                }
                $events = [];
                $events[] = [EventType::EVENT_CUSTOMER_CREATED => new \DateTime()];
                $events[] = [EventType::EVENT_EMAIL_SENT => new \DateTime()];
                $customerEvent = (new CustomerEvent())
                    ->setEvents($events);

                $customerEvent->setCustomer($entity);
                $manager->persist($customerEvent);
            }

            if($entity->getType() === AccountType::COMMERCIAL) {
                foreach ($this->getProspectData() as $prospectData) {
                    $prospect = $this->prospectFixtures->createProspect($prospectData);
                    $manager->persist($prospect);
                    $prospect->setCommercial($entity);
                }
                $i = 100;
                foreach ($this->getMichelCustomerData() as $customerData)
                {
                    $customer = $this->createAccount($customerData);
                    $manager->persist($customer);
                    $user = $this->getReference(UsersFixtures::getUserCustomerMichelReference((string) $i));
                    $user->setAccount($customer);
                    $this->addReference(self::getAccountMichelCustomerReference((string) $i), $entity);
                    $entity->setData(json_encode([
                        "address" => $faker->streetAddress,
                        "zipCode" => $faker->postcode,
                        "city" => $faker->city,
                        "country" => $faker->country,
                    ]));
                    $customer->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $entity->addCustomer($customer);
                    $this->eventDispatcher->dispatch(new CreateCustomerEvent($customer), CreateCustomerEvent::NAME);
                    ++$i;
                }
            }
            array_push($accounts, $entity);
        }

        // 100
        $i = 0;
        $iCommercial = 0;
        $iIndividual = 0;
        $iAdmin = 0;
        foreach ($this->getData() as $data) {
            $entity = $this->createAccount($data);
            $manager->persist($entity);
            /** @var User $user */
            $user = $this->getReference(UsersFixtures::getUserReference((string) $i));
            $user->setAccount($entity);
            $this->addReference(self::getAccountReference((string) $i), $entity);
            switch ($entity->getType()) {
                case AccountType::COMMERCIAL:
                    $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $this->addReference(self::getAccountCommercialReference((string) $iCommercial), $entity);
                    ++$iCommercial;
                    break;
                case AccountType::CUSTOMER:
                    $entity->setData(json_encode([
                        "address" => $faker->streetAddress,
                        "zipCode" => $faker->postcode,
                        "city" => $faker->city,
                        "country" => $faker->country,
                    ]));
                    $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $this->addReference(self::getAccountCustomerReference((string) $iIndividual), $entity);
                    $commercial = $this->getReference(self::getAccountCommercialReference((string) $iCommercial-1));
                    $commercial->addCustomer($entity);
                    $this->eventDispatcher->dispatch(new CreateCustomerEvent($entity), CreateCustomerEvent::NAME);
                    ++$iIndividual;
                    break;
                case AccountType::ADMIN:
                    $entity->setName($user->getFirstName() . ' ' . $user->getLastName());
                    $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
                    $this->addReference(self::getAccountAdminReference((string) $iAdmin), $entity);
                    ++$iAdmin;
                    break;
            }
            ++$i;
            array_push($accounts, $entity);
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
            'user_id' => UsersFixtures::MICHEL_ADMIN,
            'type' => AccountType::ADMIN,
            'name' => 'Michel Admin',
            'createdAt' => new \DateTime('2019-03-21'),
        ];
        yield [
            'user_id' => UsersFixtures::MICHEL_COMMERCIAL,
            'type' => AccountType::COMMERCIAL,
            'name' => 'Michel Commercial',
            'createdAt' => new \DateTime('2019-03-21'),
        ];
        yield [
            'user_id' => UsersFixtures::MICHEL_CUSTOMER,
            'type' => AccountType::CUSTOMER,
            'name' => 'Michel Customer',
            'paymentMethod' => json_encode([
                'card' => [
                    'brand' => 'visa',
                    'country' => 'fr',
                    "exp_month" => 05,
                    "exp_year" => 23,
                    "last4" => 4242
                ]
            ]),
            'createdAt' => new \DateTime('2019-03-21'),
        ];
    }

    private function getData(): iterable
    {
        for ($i = 0; $i < 99; ++$i) {
            switch($i % 5) {
                case 0:
                    yield $this->getCommercialData($i);
                    break;
                default:
                    yield $this->getCustomerData($i);
                    break;
            }
        }
        yield $this->getAdminData($i);
    }

    private function getMichelCustomerData(): iterable
    {
        for ($i = 0; $i < 10; ++$i) {
            yield $this->getCustomerData($i);
         }
    }

    private function getCommercialData(int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::COMMERCIAL,
        ];

        return $data;
    }

    private function getCustomerData(int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::CUSTOMER,
        ];

        return $data;
    }

    private function getAdminData(int $i): array
    {
        $data = [
            'user_id' => $i,
            'type' => AccountType::ADMIN,
        ];

        return $data;
    }

    private function getProspectData(): iterable
    {
        $faker = $this->fakerFactory;
        $slugger = new AsciiSlugger('fr');

        for ($i = 0; $i < 9; ++$i) {
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