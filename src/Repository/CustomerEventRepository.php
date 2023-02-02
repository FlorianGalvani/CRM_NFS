<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\CustomerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerEvent>
 *
 * @method CustomerEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerEvent[]    findAll()
 * @method CustomerEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerEvent::class);
    }

    public function save(CustomerEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomerEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCustomerEventsByCommercial(Account $account)
    {
        return $this->createQueryBuilder('e')
            ->select('e, c')
            ->innerJoin('e.customer', 'c')
            ->andWhere('c.commercial = :account')
            ->setParameter('account', $account)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return CustomerEvent[] Returns an array of CustomerEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CustomerEvent
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
