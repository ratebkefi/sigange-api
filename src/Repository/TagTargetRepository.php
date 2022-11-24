<?php

namespace App\Repository;

use App\Entity\TagTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TagTarget|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagTarget|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagTarget[]    findAll()
 * @method TagTarget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagTargetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagTarget::class);
    }

    // /**
    //  * @return TagTarget[] Returns an array of TagTarget objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TagTarget
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
