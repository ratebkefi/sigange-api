<?php

namespace App\Repository;

use App\Entity\DeviceModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeviceModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeviceModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeviceModel[]    findAll()
 * @method DeviceModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceModel::class);
    }

    // /**
    //  * @return DeviceModel[] Returns an array of DeviceModel objects
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
    public function findOneBySomeField($value): ?DeviceModel
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
