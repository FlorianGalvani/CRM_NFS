<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastOneByAccountAndStatus($id, Account $account, $statuses): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.customer = :account')
            ->andWhere('t.paymentStatus IN (:statuses)')
            ->setParameters(['id' => $id, 'account' => $account, 'statuses' => $statuses])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllBilledTransactionByAccount(Account $account): array
    {
        $statuses = [
            Transaction::TRANSACTION_QUOTATION_SENT,
            Transaction::TRANSACTION_QUOTATION_REQUESTED,
            Transaction::TRANSACTION_INVOICE_SENT,
            Transaction::TRANSACTION_STATUS_PAYMENT_INTENT
        ];

        return $this->createQueryBuilder('t')
            ->andWhere('t.customer = :account')
            ->andWhere('t.paymentStatus NOT IN (:statuses)')
            ->setParameters(['account' => $account, 'statuses' => $statuses])
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Transaction[] Returns an array of Transaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Transaction
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
