<?php

namespace App\Repository;

use App\Entity\DeviceModelOutput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeviceModelOutput|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeviceModelOutput|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeviceModelOutput[]    findAll()
 * @method DeviceModelOutput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceModelOutputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceModelOutput::class);
    }

    // /**
    //  * @return DeviceModelOutput[] Returns an array of DeviceModelOutput objects
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
    public function findOneBySomeField($value): ?DeviceModelOutput
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
