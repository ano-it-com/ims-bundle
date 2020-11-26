<?php

namespace ANOITCOM\IMSBundle\Repository\Incident;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Incident\IncidentStatus;

/**
 * @method IncidentStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncidentStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncidentStatus[]    findAll()
 * @method IncidentStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidentStatusRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncidentStatus::class);
    }

    // /**
    //  * @return IncidentStatus[] Returns an array of IncidentStatus objects
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
    public function findOneBySomeField($value): ?IncidentStatus
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
