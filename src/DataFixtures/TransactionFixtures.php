<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    private $fakerFactory;

    public function __construct()
    {
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AccountFixtures::class,
        ];
    }

    public static function getTransactionReference(string $key): string
    {
        return Transaction::class . '_' . $key;
    }

    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->getData() as $data) {
            $entity = new Transaction();

            $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->disableExceptionOnInvalidPropertyPath()
                ->getPropertyAccessor();

            foreach ($data as $key => $value) {
                if ($propertyAccessor->isWritable($entity, $key)) {
                    $propertyAccessor->setValue($entity, $key, $value);
                }
            }
            $manager->persist($entity);
            $this->addReference(self::getTransactionReference((string) $i), $entity);
            ++$i;
        }

        $manager->flush();
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;

        // Michel customer
        for ($i = 0; $i < 5; ++$i) {
            $createdAt = $faker->dateTimeBetween('+60 days', '+90 days');
            $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');
            yield [
                'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UserFixtures::MICHEL_CUSTOMER)),
                'amount' => $faker->numberBetween(200, 250),
                'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS,
                'stripePaymentIntentId' => 'pi_'.$faker->regexify('[0-9]{1}[A-Z]{1}[0-9]{2}[A-Z]{8}[0-9]{1}[A-Z]{2}[0-9]{2}[A-Z]{7}'),
                'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
                'label' => 'Règlement de la facture du '. $invoiceDate->format('d/m/Y'),
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
            ];
        }

        // autres fake customers
        for ($i = 0; $i < 30; ++$i) {
            $createdAt = $faker->dateTimeBetween('+60 days', '+90 days');
            yield [
                'customer' => $this->getReference(AccountFixtures::getAccountCustomerReference((string) $i)),
                'amount' => $faker->numberBetween(1, 100),
                'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS,
                'stripePaymentIntentId' => 'pi_'.$faker->regexify('[0-9]{1}[A-Z]{1}[0-9]{2}[A-Z]{8}[0-9]{1}[A-Z]{2}[0-9]{2}[A-Z]{7}'),
                'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
                'label' => 'Règlement de la facture du '. $invoiceDate->format('d/m/Y'),
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
            ];
        }
    }
}
