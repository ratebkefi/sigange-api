<?php

namespace App\Doctrine;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\EntityDisplayCustomization;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * Class EntityDisplayCustomizationExtension
 *
 * Filter the queries to keep only the entities where the owner is the connected User.
 * Used for GET EntityDisplayCustomization collection operations.
 *
 * @package App\Doctrine
 */
final class EntityDisplayCustomizationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->addWhereRestricted($queryBuilder, $resourceClass);
    }

    private function addWhereRestricted(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }
        // Filter only EntityDisplayCustomization entities
        if ($resourceClass !== EntityDisplayCustomization::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf("%s.owner = (:user)", $rootAlias))
            ->setParameter("user", $user);


    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        $this->addWhereRestricted($queryBuilder, $resourceClass);
    }
}
