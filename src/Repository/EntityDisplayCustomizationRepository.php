<?php

namespace App\Repository;

use App\Entity\EntityDisplayCustomization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntityDisplayCustomization|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityDisplayCustomization|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityDisplayCustomization[]    findAll()
 * @method EntityDisplayCustomization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityDisplayCustomizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityDisplayCustomization::class);
    }

    /**
     * Get all the EntityDisplayCustomization having for owner the owner and entity class name of the given EDC and set
     * these to default false
     * @param EntityDisplayCustomization $entityDisplayCustomization
     * @throws ORMException
     */
    public function handleDefaultStatus(EntityDisplayCustomization $entityDisplayCustomization): void
    {
        $em = $this->getEntityManager();

        $entityDisplayCustomizations = $em->createQueryBuilder()->select('c')
            ->from('App:EntityDisplayCustomization', 'c')
            ->where('c.owner = :owner')
            ->setParameter('owner', $entityDisplayCustomization->getOwner())
            ->andWhere('c.entityClassName = :entityClassName')
            ->setParameter('entityClassName', $entityDisplayCustomization->getEntityClassName())
            ->andWhere('c.code != :entityDisplayCustomCustomizationCode')
            ->setParameter('entityDisplayCustomCustomizationCode',
                $entityDisplayCustomization->getCode()->toBinary())
            ->getQuery()
            ->getResult();

        // Set all other EntityDisplayCustomizations that the user owns for this entityClassName to default false
        if ($entityDisplayCustomizations && count($entityDisplayCustomizations) > 0) {
            foreach ($entityDisplayCustomizations as $entity) {
                $entity->setIsDefault(false);
                try {
                    $em->persist($entity);
                } catch (ORMException $e) {
                    throw new ORMException($e->getMessage());
                }

            }
        }
    }
}
