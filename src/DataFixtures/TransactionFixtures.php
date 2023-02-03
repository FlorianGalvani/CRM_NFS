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

            if(in_array($entity->getPaymentStatus(), [
                Transaction::TRANSACTION_QUOTATION_SENT,
                Transaction::TRANSACTION_QUOTATION_REQUESTED,
                Transaction::TRANSACTION_STATUS_PAYMENT_FAILURE,
                Transaction::TRANSACTION_STATUS_PAYMENT_ABANDONED,
                Transaction::TRANSACTION_STATUS_PAYMENT_INTENT
            ])) {
                $quotation = $this->createDocument($entity);
                $quotation->setType(Document::TRANSACTION_DOCUMENT_QUOTATION);
                $quotation->setTransaction($entity);
                $manager->persist($quotation);
                $entity->setTransactionQuotation($quotation);
            } else {
                $invoice = $this->createDocument($entity);
                $invoice->setType(Document::TRANSACTION_DOCUMENT_INVOICE);
                $invoice->setTransaction($entity);
                $manager->persist($invoice);
                $entity->setTransactionInvoice($invoice);
            }

            ++$i;
        }

        $manager->flush();
    }

    private function createDocument(Transaction $entity): Document
    {
        $faker = $this->fakerFactory;
        $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');

        $productLines = [];
        for ($j = 0; $j < 3; ++$j) {
            $productLines[] = [
                "description" => $faker->words($faker->numberBetween(1, 10), true),
                "quantity" => $faker->numberBetween(1, 10),
                "rate" => $faker->numberBetween(35, 5000)
            ];
        }
        
        $document = new Document();

        $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');
        $dueInterval =  $faker->dateTimeBetween('+90 days', '+115 days');
        $document->setCustomer($entity->getCustomer());
        $document->setCommercial($entity->getCustomer()->getCommercial());
        $document->setTransaction($entity);
        $document->setFileExtension($faker->randomElement(['dot', 'pdf', 'png', 'jpg']));
        $document->setFileName('doc-'.$invoiceDate->format('d-m-Y'));
        $document->setData(json_encode([
            "logoWidth" => 100,
            "title" => "Devis",
            "companyName" => "NFS",
            "name" => $entity->getCustomer()->getCommercial()->getName(),
            "companyAddress" => $entity->getCustomer()->getCommercial()->getUser()->getAddress(),
            "companyAddress2" => "Rouen,76000",
            "companyCountry" => "United States",
            "billTo" => "Facturé à: ",
            "clientName" => $entity->getCustomer()->getName(),
            "clientAddress" => "14 rue du bonheur",
            "clientAddress2" => "Rouen,76000",
            "clientCountry" => "France",
            "invoiceTitleLabel" => "Devis#",
            "invoiceTitle" => "DEV-" . $invoiceDate->format('d-m-Y') . "-" . $faker->numberBetween(100, 999),
            "invoiceDateLabel" => "Date du devis",
            "invoiceDate" => $invoiceDate,
            "invoiceDueDateLabel" => "Date d'échéance",
            "invoiceDueDate" =>  $dueInterval,
            "productLineDescription" => "Description",
            "productLineQuantity" => "Qté",
            "productLineQuantityRate" => "Prix unitaire",
            "productLineQuantityAmount" => "Montant",
            "productLines" => $productLines,
            "subTotalLabel" => "Sous-total",
            "taxLabel" => "TVA (20%)",
            "totalLabel" => "TOTAL",
            "currency" => "€",
            "notesLabel" => "Notes",
            "notes" => "Merci pour votre confiance!",
            "termLabel" => "Termes & Conditions",
            "term" => null,
            "status" => $entity->getPaymentStatus()
        ]));

        return $document;
    }

    private function getData(): iterable
    {
        $faker = $this->fakerFactory;

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $stipePaymentId = substr( str_shuffle( $chars ), 0, 24 );

        $createdAt = $faker->dateTimeBetween('+60 days', '+90 days');

        // Michel customer
        for ($i = 0; $i < 5; ++$i) {
            $invoiceDate = $faker->dateTimeBetween('+60 days', '+85 days');
            yield [
                'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UsersFixtures::MICHEL_CUSTOMER)),
                'amount' => $faker->numberBetween(200, 250),
                'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS,
                'stripePaymentIntentId' => 'pi_'.$stipePaymentId,
                'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
                'label' => 'Règlement d\'une facture',
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
            ];
        }

        yield [
            'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UsersFixtures::MICHEL_CUSTOMER)),
            'amount' => $faker->numberBetween(200, 250),
            'paymentStatus' => Transaction::TRANSACTION_INVOICE_SENT,
            'stripePaymentIntentId' => null,
            'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
            'label' => 'Envoie d\'une facture',
            'createdAt' => $createdAt,
            'updatedAt' => $createdAt,
        ];

        yield [
            'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UsersFixtures::MICHEL_CUSTOMER)),
            'amount' => $faker->numberBetween(200, 250),
            'paymentStatus' => Transaction::TRANSACTION_QUOTATION_SENT,
            'stripePaymentIntentId' => null,
            'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
            'label' => 'Envoie d\'un devis',
            'createdAt' => $createdAt,
            'updatedAt' => $createdAt,
        ];

        yield [
            'customer' => $this->getReference(AccountFixtures::getAccountMichelReference(UsersFixtures::MICHEL_CUSTOMER)),
            'amount' => $faker->numberBetween(200, 250),
            'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_FAILURE,
            'stripePaymentIntentId' => null,
            'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
            'label' => 'Echec de paiement',
            'createdAt' => $createdAt,
            'updatedAt' => $createdAt,
        ];

        // autres fake customers
        for ($i = 0; $i < 30; ++$i) {
            $createdAt = $faker->dateTimeBetween('+60 days', '+90 days');
            yield [
                'customer' => $this->getReference(AccountFixtures::getAccountCustomerReference((string) $i)),
                'amount' => $faker->numberBetween(1, 100),
                'paymentStatus' => Transaction::TRANSACTION_STATUS_PAYMENT_SUCCESS,
                'stripePaymentIntentId' => 'pi_'.$stipePaymentId,
                'type' => 'On ne sait pas encore ce qui est vendu sur ce truc',
                'label' => 'Règlement d\'une facture ',
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt,
            ];
        }
    }
}
