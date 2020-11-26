<?php

namespace ANOITCOM\IMSBundle\Repository\Incident\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionStatus;

/**
 * @method ActionStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionStatus[]    findAll()
 * @method ActionStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionStatusRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionStatus::class);
    }

    // /**
    //  * @return ActionStatus[] Returns an array of ActionStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActionStatus
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
