<?php

namespace ANOITCOM\IMSBundle\Repository\Incident\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTask;

/**
 * @method ActionTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionTask[]    findAll()
 * @method ActionTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionTaskRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionTask::class);
    }

    // /**
    //  * @return ActionTask[] Returns an array of ActionTask objects
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
    public function findOneBySomeField($value): ?ActionTask
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
