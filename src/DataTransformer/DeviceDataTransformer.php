<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\DeviceInput;
use App\Entity\Device;
use App\Service\TagGroupHandler;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class DeviceDataTransformer implements DataTransformerInitializerInterface
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
        $device = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new Device();

        try {
            // @var Device
            $device = $this->tagGroupHandler->handleTagGroups($input, $device);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }

        $device->setEnabled($input->enabled);
        $device->setName($input->name);
        $device->setCode($input->code);
        $device->setUserGroup($input->userGroup);
        $device->setModel($input->model);
        $device->setSite($input->site);
        $device->setNetwork($input->network);
        $device->setPlatform($input->platform);
        $device->setComment($input->comment);
        $device->setInternalComment($input->internalComment);
        if ($input->serialNumber) {
            $device->setSerialNumber($input->serialNumber);
        }
        $device->setMacAddress($input->macAddress);
        if ($input->osVersion) {
            $device->setOsVersion($input->osVersion);
        }
        if ($input->wantedOsVersion) {
            $device->setWantedOsVersion($input->wantedOsVersion);
        }
        if ($input->softwareVersion) {
            $device->setSoftwareVersion($input->softwareVersion);
        }
        if ($input->wantedSoftwareVersion) {
            $device->setWantedSoftwareVersion($input->wantedSoftwareVersion);
        }
        $device->setIsSshEnabled($input->isSshEnabled);
        $device->setIsVpnEnabled($input->isVpnEnabled);
        if ($input->diagnostic) {
            $device->setDiagnostic($input->diagnostic);
        }
        $device->setStatus($input->status);
        foreach ($input->outputs as $output) {
            $device->addOutput($output);
        }

        return $device;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a Device we transformed the data already
        if ($data instanceof Device) {
            return false;
        }
        return Device::class === $to && null !== ($context['input']['class'] ?? null);
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
            return new DeviceInput();
        }
        // For a PUT or PATCH
        $deviceData = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a DeviceInput based on the current resource's data
        $dto = new DeviceInput();

        $dto->enabled = $deviceData->isEnabled();
        $dto->name = $deviceData->getName();
        $dto->code = $deviceData->getCode();
        $dto->userGroup = $deviceData->getUserGroup();
        $dto->model = $deviceData->getModel();
        $dto->site = $deviceData->getSite();
        $dto->network = $deviceData->getNetwork();
        $dto->platform = $deviceData->getPlatform();
        $dto->comment = $deviceData->getComment();
        $dto->internalComment = $deviceData->getInternalComment();
        $dto->serialNumber = $deviceData->getSerialNumber();
        $dto->macAddress = $deviceData->getMacAddress();
        $dto->osVersion = $deviceData->getOsVersion();
        $dto->wantedOsVersion = $deviceData->getWantedOsVersion();
        $dto->softwareVersion = $deviceData->getSoftwareVersion();
        $dto->wantedSoftwareVersion = $deviceData->getWantedSoftwareVersion();
        $dto->isSshEnabled = $deviceData->getIsSshEnabled();
        $dto->isVpnEnabled = $deviceData->getIsVpnEnabled();
        $dto->diagnostic = $deviceData->getDiagnostic();
        $dto->status = $deviceData->getStatus();
        $dto->outputs = $deviceData->getOutputs();
        $dto->userGroup = $deviceData->getUserGroup();

        return $dto;

    }
}
