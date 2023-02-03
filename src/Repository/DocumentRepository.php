<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function save(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Document $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllInvoicesByAccount(Account $account): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.customer = :account')
            ->andWhere('d.type = :type')
            ->setParameters(['account' => $account, 'type' => 'facture'])
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findLastOneCurrentInvoiceByAccount(Account $account, array $statuses): ?Document
    {
        return $this->createQueryBuilder('d')
            ->select('d, t')
            ->innerJoin('d.transaction', 't')
            ->where('d.customer = :account')
            ->andWhere('t.paymentStatus IN (:statuses)')
            ->andWhere('d.type = :type')
            ->setParameters(['account' => $account, 'type' => 'facture', 'statuses' => $statuses])
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastInvoicesByAccountAndStatus(Account $account, array $statuses): array
    {
        return $this->createQueryBuilder('d')
            ->select('d, t')
            ->innerJoin('d.transaction', 't')
            ->where('d.customer = :account')
            ->andWhere('t.paymentStatus IN (:statuses)')
            ->andWhere('d.type = :type')
            ->setParameters(['account' => $account, 'type' => 'facture', 'statuses' => $statuses])
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        ;
    }

    public function findAllQuotesByAccount(Account $account): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.customer = :account')
            ->andWhere('d.type = :type')
            ->setParameters(['account' => $account, 'type' => 'devis'])
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findLastQuotesByAccount(Account $account): array{
        return $this->createQueryBuilder('d')
            ->where('d.customer = :account')
            ->andWhere('d.type = :type')
            ->setParameters(['account' => $account, 'type' => 'devis'])
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLastInvoicesByAccount(Account $account): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.commercial = :account')
            ->setParameter('account', $account)
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Document[] Returns an array of Document objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Document
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
