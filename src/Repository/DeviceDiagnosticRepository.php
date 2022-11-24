<?php

namespace App\Repository;

use App\Entity\DeviceDiagnostic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeviceDiagnostic|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeviceDiagnostic|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeviceDiagnostic[]    findAll()
 * @method DeviceDiagnostic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceDiagnosticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceDiagnostic::class);
    }

    // /**
    //  * @return DeviceDiagnostic[] Returns an array of DeviceDiagnostic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeviceDiagnostic
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
