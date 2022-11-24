<?php

namespace App\Repository;

use App\Entity\DeviceOutput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeviceOutput|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeviceOutput|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeviceOutput[]    findAll()
 * @method DeviceOutput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceOutputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceOutput::class);
    }

    // /**
    //  * @return DeviceOutput[] Returns an array of DeviceOutput objects
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
    public function findOneBySomeField($value): ?DeviceOutput
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
