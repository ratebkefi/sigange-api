<?php


namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;

/**
 * To replace by a core ApiPlatform implementation when/if available
 * Source: https://gist.github.com/Tersoal/d45b0cc75cadf72cd7c0e49b892809b3
 * Original author: @Tersoal
 * inspired from :
 *      - https://gist.github.com/masseelch/47931f3a745409f8f44c69efa9ecb05c
 *      - https://gist.github.com/renta/b6ece3fec7896440fe52a9ec0e76571a
 *      - https://gist.github.com/masacc/94df641b3cb9814cbdaeb3f158d2e1f7
 *
 * If more than one group of properties is used, the query is made with an 'AND' (e.g. search_example1 AND search_example2 here)
 * Inside each group (here 'search_example1' for example) an 'OR' is used for all the given properties (e.g. site.name, name)
 * The same group can be used several times for an 'or' query: '&search_text_site[]=villiers&search_text_site[]=montmartre'
 * How to use :
 *     - add classAnnotation on an entity:
 *         ApiFilter(FullTextSearchFilter::class, properties={
 *             "search_example1"={
 *                 "property1": "partial",
 *                 "property2": "exact"
 *             },
 *             "search_example2"={
 *                 "property1": "partial",
 *                 "property3": "partial"
 *             }
 *         })
 *     - use filter in query string as:
 *          + `/api/myentity?search_example1=String%20with%20spaces` => this will search "String with spaces"
 *          + `/api/myentity?search_example1%5B%5D=String%20with%20spaces` => this will search "String with spaces"
 *          + `/api/myentity?search_example1%5B%5D=String&search_example1%5B%5D=with&search_example1%5B%5D=spaces` => this will search "String" or "with" or "spaces"
 * TODO: not working with 'code' fields for now
 */
class OrSearchFilter extends SearchFilter
{
    private const PROPERTY_NAME_PREFIX = 'search_';

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        // Only handle search properties that starts with PROPERTY_NAME_PREFIX
        if (0 !== strpos($property, self::PROPERTY_NAME_PREFIX)) {
            return;
        }

        // Only handle search properties that have properties values
        if (false === isset($this->properties[$property])) {
            return;
        }

        // Get the search terms, only handle not null values
        $values = $this->normalizeValues((array)$value, $property);
        if (null === $values) {
            return;
        }

        $orExpressions = [];
        // The searched terms
        foreach ($values as $index => $value) {
            // The properties searched
            foreach ($this->properties[$property] as $propertyName => $strategy) {
                $strategy = $strategy ?? self::STRATEGY_EXACT;
                $alias = $queryBuilder->getRootAliases()[0];
                $field = $propertyName;

                // Handle relations. For searches on 'device.site.name' for example
                $associations = [];
                if ($this->isPropertyNested($propertyName, $resourceClass)) {
                    [$alias, $field, $associations] = $this->addJoinsForNestedProperty($propertyName, $alias,
                        $queryBuilder, $queryNameGenerator, $resourceClass);
                }

                $caseSensitive = true;
                $metadata = $this->getNestedMetadata($resourceClass, $associations);
                // Check that the field exists and has a valid value then creates an array of 'or' expressions
                if ($metadata->hasField($field)) {
                    if ('id' === $field) {
                        $value = $this->getIdFromValue($value);
                    }

                    if (!$this->hasValidValues((array)$value,
                        $this->getDoctrineFieldType($propertyName, $resourceClass))) {
                        $this->logger->notice('Invalid filter ignored', [
                            'exception' => new InvalidArgumentException(sprintf('Values for field "%s" are not valid according to the doctrine type.',
                                $field)),
                        ]);
                        continue;
                    }

                    // prefixing the strategy with 'i' makes it case insensitive
                    if (0 === strpos($strategy, 'i')) {
                        $strategy = substr($strategy, 1);
                        $caseSensitive = false;
                    }

                    $orExpressions[] = $this->addWhereByStrategy($strategy, $queryBuilder, $queryNameGenerator, $alias,
                        $field, $value, $caseSensitive);
                }
            }
        }
        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$orExpressions));
    }

    /**
     * {@inheritDoc}
     */
    protected function addWhereByStrategy(
        string $strategy,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $field,
        $value,
        bool $caseSensitive
    ) {
        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = $queryNameGenerator->generateParameterName($field);
        $exprBuilder = $queryBuilder->expr();

        $queryBuilder->setParameter($valueParameter, $value);

        switch ($strategy) {
            case null:
            case self::STRATEGY_EXACT:
                return $exprBuilder->eq($wrapCase("$alias.$field"), $wrapCase(":$valueParameter"));
            case self::STRATEGY_PARTIAL:
                return $exprBuilder->like($wrapCase("$alias.$field"),
                    $exprBuilder->concat("'%'", $wrapCase(":$valueParameter"), "'%'"));
            case self::STRATEGY_START:
                return $exprBuilder->like($wrapCase("$alias.$field"),
                    $exprBuilder->concat($wrapCase(":$valueParameter"), "'%'"));
            case self::STRATEGY_END:
                return $exprBuilder->like($wrapCase("$alias.$field"),
                    $exprBuilder->concat("'%'", $wrapCase(":$valueParameter")));
            case self::STRATEGY_WORD_START:
                return $exprBuilder->orX(
                    $exprBuilder->like($wrapCase("$alias.$field"),
                        $exprBuilder->concat($wrapCase(":$valueParameter"), "'%'")),
                    $exprBuilder->like($wrapCase("$alias.$field"),
                        $exprBuilder->concat("'%'", $wrapCase(":$valueParameter")))
                );
            default:
                throw new InvalidArgumentException(sprintf('strategy %s does not exist.', $strategy));
        }
    }

    /**
     * Generate an openapi documentation for the filter
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $descriptions = [];

        foreach ($this->properties as $filterName => $properties) {
            $propertyNames = [];

            foreach ($properties as $property => $strategy) {
                if (!$this->isPropertyMapped($property, $resourceClass, true)) {
                    continue;
                }

                $propertyNames[] = $this->normalizePropertyName($property);
            }

            $filterParameterName = $filterName . '[]';
            $descriptions[$filterParameterName] = [
                'property' => $filterName,
                'type' => 'string',
                'required' => false,
                'is_collection' => true,
                'description' => "Search involves the fields: `" . implode('`,` ', $propertyNames) . "`",
                'openapi' => [
                    'example' => "\n - `/api/devices?search_text[]=String%20with%20spaces` to search devices with text 'String with spaces' with an *OR* on selected fields.\n\n - `/api/devices?search_text[]=rome&search_text[]=paris` to search devices with text 'rome' or 'paris' with an *OR* on selected fields. .\n\n - `/api/devices?search_text_desc[]=rome&search_text_name[]=paris` to search devices with text 'rome' for field `desc` *and* text 'paris' for field `name`."
                ],
            ];
        }

        return $descriptions;
    }
}
