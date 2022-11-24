<?php

namespace App\Repository;

use App\Entity\DeviceStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeviceStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeviceStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeviceStatus[]    findAll()
 * @method DeviceStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceStatus::class);
    }

    // /**
    //  * @return DeviceStatus[] Returns an array of DeviceStatus objects
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
    public function findOneBySomeField($value): ?DeviceStatus
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
