<?php

namespace ANOITCOM\IMSBundle\Repository\Incident\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTaskStatus;

/**
 * @method ActionTaskStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionTaskStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionTaskStatus[]    findAll()
 * @method ActionTaskStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionTaskStatusRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionTaskStatus::class);
    }

    // /**
    //  * @return ActionTaskStatus[] Returns an array of ActionTaskStatus objects
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
    public function findOneBySomeField($value): ?ActionTaskStatus
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
