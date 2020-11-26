<?php

namespace ANOITCOM\IMSBundle\Repository\Incident\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTaskType;

/**
 * @method ActionTaskType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionTaskType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionTaskType[]    findAll()
 * @method ActionTaskType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionTaskTypeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionTaskType::class);
    }

    // /**
    //  * @return ActionTaskType[] Returns an array of ActionTaskType objects
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
    public function findOneBySomeField($value): ?ActionTaskType
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
