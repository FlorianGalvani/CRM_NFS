<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\Document;
use App\Entity\User;
use App\Enum\Document\DocumentType;
use App\Event\CreateDocumentEvent;
use App\Repository\AccountRepository;
use App\Repository\DocumentRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class DocumentController extends BaseController
{
    private $accountRepo;
    private $documentRepo;
    
    public function __construct(AccountRepository $accountRepo, DocumentRepository $documentRepo) 
    {
        $this->accountRepo = $accountRepo;
        $this->documentRepo = $documentRepo;
    }



    #[Route('/commercial/invoices/formdata')]
    function getFormData()
    {
        $response = [
            'success' => false
        ];

        $currentUser = $this->getUser();

        $formData = [
            'commercial' => [
                'firstname' => $currentUser->getFirstname(),
                'lastname' => $currentUser->getLastname(),
            ],
            'company' => json_decode($currentUser->getAccount()->getData(),true),
        ];

        $customers = $this->accountRepo->findBy(['commercial' => $currentUser->getAccount()]);
        $customersData = [];
        $customersLabels = [];
        foreach ($customers as $customer) {
            $customerData = json_decode($customer->getData(), true);
            $customersData[] = [
                'data' => array(
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'address' => $customerData['address'],
                    'zip' => $customerData['zipCode'],
                    'city' => $customerData['city'],
                    'country' => $customerData['country'],
                )
            ];
            $customersLabels[] = $customer->getName();
        }
        $formData['customers'] = $customersData;
        $formData['customersLabels'] = $customersLabels;
        $response['formData'] = $formData;
        $response['success'] = true;

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route('/commercial/invoice', methods: ['POST'])]
    public function create(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $response = [
            'success' => false
        ];

        if(!$request->isXmlHttpRequest()) {
            return $this->json($response, Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);
            unset($data['invoice']['logo']);

            $currentUser = $this->getUser();
            $customer = $this->accountRepo->find($data['customer']);
            $commercial = $currentUser->getAccount();

            $document = new Document();
            $document->setType(DocumentType::INVOICE);
            $document->setFileName('Facture ');
            $document->setFileExtension('pdf');
            $document->setCustomer($customer);
            $document->setCommercial($commercial);

            $document->setData(json_encode($data['invoice']));

            $this->documentRepo->save($document, true);

            $response['success'] = true;

            $eventDispatcher->dispatch(new CreateDocumentEvent($document), CreateDocumentEvent::NAME);

            return $this->json($response,Response::HTTP_OK);
        } catch(Error $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/commercial-invoices')]
    public function commercialInvoices()
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentAccount = $user->getAccount();

        $invoices = $this->documentRepo->findLastInvoicesByAccount($currentAccount);

        $invoicesData = [];

        foreach($invoices as $_invoices) {
            array_push($invoicesData, $_invoices->getInfos());
        }

        try {
            return $this->json($invoicesData);
        } catch(Error $e) {
            http_response_code(500);

            return $this->json(['error' => $e->getMessage()]);
        }
    }
}