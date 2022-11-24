<?php

namespace App\Repository;

use App\Entity\TemplateModelOutput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateModelOutput|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateModelOutput|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateModelOutput[]    findAll()
 * @method TemplateModelOutput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateModelOutputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateModelOutput::class);
    }

    // /**
    //  * @return TemplateModelOutput[] Returns an array of TemplateModelOutput objects
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
    public function findOneBySomeField($value): ?TemplateModelOutput
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
