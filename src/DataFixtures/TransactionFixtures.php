<?php

namespace App\DataFixtures;

use App\Entity\Document;
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
            UsersFixtures::class,
            AccountFixtures::class,
        ];
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

            $quotation = $this->createDocument($entity);
            $quotation->setType(Document::TRANSACTION_DOCUMENT_QUOTATION);
            $manager->persist($quotation);

            $invoice = $this->createDocument($entity);
            $invoice->setType(Document::TRANSACTION_DOCUMENT_INVOICE);
            $manager->persist($invoice);

            $entity->setTransactionQuotation($quotation);
            $entity->setTransactionInvoice($invoice);

            ++$i;
        }

        $manager->flush();
    }

    private function createDocument(Transaction $entity): Document
    {
        $faker = $this->fakerFactory;
        $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');

        $document = new Document();

        $document->setCustomer($entity->getCustomer());
        $document->setCommercial($entity->getCustomer());
        $document->setTransaction($entity);
        $document->setFileExtension($faker->randomElement(['dot', 'pdf', 'png', 'jpg']));
        $document->setFileName('doc-'.$invoiceDate->format('d-m-Y'));

        return $document;
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $stipePaymentId = substr( str_shuffle( $chars ), 0, 24 );

        // Michel customer
        for ($i = 0; $i < 5; ++$i) {
            $createdAt = $faker->dateTimeBetween('+60 days', '+90 days');
            $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');
            yield [
                'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UsersFixtures::MICHEL_CUSTOMER)),
                'amount' => $faker->numberBetween(200, 250),
                'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS,
                'stripePaymentIntentId' => 'pi_'.$stipePaymentId,
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
                'stripePaymentIntentId' => 'pi_'.$stipePaymentId,
                'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
                'label' => 'Règlement de la facture du '. $invoiceDate->format('d/m/Y'),
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
            ];
        }
    }
}
