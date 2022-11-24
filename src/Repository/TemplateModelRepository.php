<?php

namespace App\Repository;

use App\Entity\TemplateModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateModel[]    findAll()
 * @method TemplateModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateModel::class);
    }

    // /**
    //  * @return TemplateModel[] Returns an array of TemplateModel objects
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
    public function findOneBySomeField($value): ?TemplateModel
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
