<?php


namespace App\ApiPlatform;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;

/**
 *
 * source : https://github.com/webstacknl/api-platform-extensions-bundle/blob/master/Filter/UuidFilter.php
 * Class UuidFilter
 * @package App\ApiPlatform
 */
class UuidFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property,
                                      $value, QueryBuilder $queryBuilder,
                                      QueryNameGeneratorInterface $queryNameGenerator,
                                      string $resourceClass,
                                      string $operationName = null)
    {
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator,
                $resourceClass);
        }

        $valueParameter = $queryNameGenerator->generateParameterName($field);

        if (is_array($value)) {

            $queryBuilder
                ->andWhere(sprintf('%s.%s IN (:%s)', $alias, $field, $valueParameter))
                ->setParameter($valueParameter, array_map(static function ($uuid) {
                    preg_match('/[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid, $match);

                    if (!empty($match[0])) {
                        $uuid = $match[0];
                    }

                    return Uuid::fromString($uuid)->toBinary();
                }, $value));
        } else {
            preg_match('/[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $value, $match);

            if (!empty($match[0])) {
                $value = $match[0];
            }

            $queryBuilder
                ->andWhere(sprintf('%s.%s IN (:%s)', $alias, $field, $valueParameter))
                ->setParameter($valueParameter, $value, 'uuid');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();

        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $unused) {
            if (!$this->isPropertyMapped($property, $resourceClass)) {
                continue;
            }

            $filterParameterNames = [$property, $property . '[]'];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $property,
                    'type' => 'uuid',
                    'required' => false,
                    'strategy' => 'exact',
                    'is_collection' => '[]' === substr((string)$filterParameterName, -2),
                    'swagger' => [
                        'type' => 'uuid',
                    ],
                ];
            }
        }

        return $description;
    }
}
