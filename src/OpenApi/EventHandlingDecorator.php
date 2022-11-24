<?php
// api/src/OpenApi/JwtDecorator.php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model;
use ApiPlatform\Core\OpenApi\OpenApi;

final class EventHandlingDecorator implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $pathItem = new Model\PathItem(
            'Event handling',
            'Handle the creation of a custom Event',
            null,
            null,
            null,
            new Model\Operation(
                'postEventHandling',
                ['Events'],
                [
                    '200' => [
                        'description' => 'The entity targeted by the custom event',
                        'content' => [
                            'application/json' => [

                            ],
                        ],


                    ],



                ],
                  'Handle the creation of a custom Event',
                'Create a custom event for a watchable entity',
                null,
                [],
                new Model\RequestBody(
                    'Create a new custom event',
                    new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' =>
                                    [
                                        'entityClassName' => ['type' => 'string'],
                                        'code' => ['type' => 'string'],
                                    ],
                            ],
                            'example' => [
                                'entityClassName' => 'site',
                                'code' => '7d3778c0-9875-490e-bad6-3ac2472e9060',
                            ],
                        ],
                    ]),
                ),
            ),
        );

        $openApi->getPaths()->addPath('/api/entity_events', $pathItem);


        return $openApi;
    }
}
