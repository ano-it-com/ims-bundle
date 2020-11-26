<?php

namespace ANOITCOM\IMSBundle\Repository\Security;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ANOITCOM\IMSBundle\Entity\Security\GroupPermission;

/**
 * @method GroupPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupPermission[]    findAll()
 * @method GroupPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupPermissionRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupPermission::class);
    }

    // /**
    //  * @return GroupPermission[] Returns an array of GroupPermission objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupPermission
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
