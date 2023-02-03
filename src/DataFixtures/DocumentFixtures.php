<?php

namespace App\DataFixtures;

use App\Entity\CustomerEvent;
use App\Entity\Document;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Enum\Customer\EventType;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class DocumentFixtures extends Fixture implements DependentFixtureInterface
{
    private $fakerFactory;

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            AccountFixtures::class,
        ];
    }

    public function __construct()
    {
        $this->fakerFactory = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        echo 'Faut être patient, ça va prendre un peu de temps...';
        $faker = $this->fakerFactory;
        $customer = $manager->getRepository(Account::class)->findOneBy(['name' => 'Michel Customer']);
        
        $commercial = $manager->getRepository(Account::class)->findOneBy(['name' => 'Michel Commercial']);
        $entityCount = 100;
    
        for ($i = 0; $i < $entityCount; $i++) {
            
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
            $randomCustomerAccount = $manager->getRepository(Account::class)->findBy(['type' => 'customer']);
            shuffle($randomCustomerAccount);
            $document->setCustomer($randomCustomerAccount[0]); 
            $document->setCommercial($commercial);
            $document->setType(Document::TRANSACTION_DOCUMENT_QUOTATION);
            $document->setFileExtension($faker->randomElement(['dot', 'pdf', 'png', 'jpg']));
            $document->setFileName('doc-' . $invoiceDate->format('d-m-Y'));
            $document->setData(json_encode([
                "logoWidth" => 100,
                "title" => "Devis",
                "companyName" => "NFS",
                "name" => $commercial->getName(),
                "companyAddress" => $randomCustomerAccount[0]->getUser()->getAddress(),
                "companyAddress2" => "Rouen,76000",
                "companyCountry" => "United States",
                "billTo" => "Facturé à: ",
                "clientName" => $randomCustomerAccount[0]->getName(),
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
                "term" => null
            ]));
            $manager->persist($document);

            $customerEvent = $manager->getRepository(CustomerEvent::class)->findOneBy(['customer' => $document->getCustomer()]);
            $transaction = (new Transaction())
                ->setCustomer($document->getCustomer())
                ->setPaymentStatus(Transaction::TRANSACTION_QUOTATION_SENT)
                ->setTransactionQuotation($document)
                ->setLabel('Envoie d\'un devis ')
                ->setType('')
                ->setAmount($faker->numberBetween(200, 2500));
            $_event = [EventType::EVENT_QUOTATION_SENT => new \DateTime()];

            $events = $customerEvent->getEvents();
            $events[] = $_event;
            $customerEvent->setEvents($events);

            $manager->persist($transaction);
            $document->setTransaction($transaction);
            $manager->persist($customerEvent);
        }
        $manager->flush();
    }
}
