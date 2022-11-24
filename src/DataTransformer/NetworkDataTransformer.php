<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\DeviceInput;
use App\Dto\NetworkInput;
use App\Entity\Device;
use App\Entity\Network;
use App\Service\TagGroupHandler;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class NetworkDataTransformer implements DataTransformerInitializerInterface
{
    private TagGroupHandler $tagGroupHandler;

    public function __construct(TagGroupHandler $tagGroupHandler)
    {
        $this->tagGroupHandler = $tagGroupHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($input, string $to, array $context = [])
    {
        $isPopulated = isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE]);
        $network = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new Network();

        try {
            // @var Network
            $network = $this->tagGroupHandler->handleTagGroups($input, $network);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }


        $network->setCode($input->code);
        $network->setName($input->name);
        $network->setDescription($input->description);
        if ($input->publicIpV4) {
            $network->setPublicIpV4($input->publicIpV4);
        }
        if ($input->publicIpV6) {
            $network->setPublicIpV6($input->publicIpV6);
        }
        if ($input->gatewayIpV4) {
            $network->setGatewayIpV4($input->gatewayIpV4);
        }
        if ($input->gatewayIpV6) {
            $network->setGatewayIpV6($input->gatewayIpV6);
        }
        if ($input->ssh) {
            $network->setSsh($input->ssh);
        }
        $network->setComment($input->comment);
        foreach ($input->sites as $site) {
            $network->addSite($site);
        }
        $network->setUserGroup($input->userGroup);
        $network->setEnabled($input->enabled);

        return $network;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a Network we transformed the data already
        if ($data instanceof Network) {
            return false;
        }
        return Network::class === $to && null !== ($context['input']['class'] ?? null);
    }

    /**
     * @param string $inputClass
     * @param array $context
     * @return object|null
     */
    public function initialize(string $inputClass, array $context = []): ?object
    {
        $isPopulated = isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE]);
        // For a POST
        if (!$isPopulated) {
            // initialize an empty Dto
            return new NetworkInput();
        }
        // For a PUT or PATCH
        $networkData = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a NetworkInput based on the current resource's data
        $dto = new NetworkInput();

        $dto->code = $networkData->getCode();
        $dto->name = $networkData->getName();
        $dto->description = $networkData->getDescription();
        $dto->publicIpV4 = $networkData->getPublicIpV4();
        $dto->publicIpV6 = $networkData->getPublicIpV6();
        $dto->gatewayIpV4 = $networkData->getGatewayIpV4();
        $dto->gatewayIpV6 = $networkData->getGatewayIpV6();
        $dto->ssh = $networkData->getSsh();
        $dto->comment = $networkData->getComment();
        $dto->sites = $networkData->getSites();
        $dto->userGroup = $networkData->getUserGroup();
        $dto->enabled = $networkData->isEnabled();

        return $dto;

    }
}
