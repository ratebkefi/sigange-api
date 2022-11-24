<?php
namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model;

class OpenApiFactory implements OpenApiFactoryInterface
{
    private $decorated;

    public function __construct(OpenApiFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $pathItem = $openApi->getPaths()->getPath('/api/devices/{macAddress}/outputs');
        $operation = $pathItem->getGet();

        // Only add the macAddress as a required query param for this path (not adding the "Code" that is added by default)
        $openApi->getPaths()->addPath('/api/devices/{macAddress}/outputs', $pathItem->withGet(
            $operation->withParameters(array_merge(
                [new Model\Parameter('macAddress', 'path', 'The macAddress of the Device', true)]
            ))
        ));


        return $openApi;
    }
}
