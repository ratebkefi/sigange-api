<?php


namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\SiteInput;
use App\Entity\Site;
use App\Service\TagGroupHandler;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class SiteDataTransformer implements DataTransformerInitializerInterface
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
        $site = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new Site();

        try {
            // @var Site
            $site = $this->tagGroupHandler->handleTagGroups($input, $site);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }

        $site->setCode($input->code);
        $site->setName($input->name);
        if ($input->externalId) {
            $site->setExternalId($input->externalId);
        }
        $site->setDescription($input->description);
        $site->setUserGroup($input->userGroup);
        $site->setTemplateModel($input->templateModel);
        $site->setContact($input->contact);
        $site->setAddress($input->address);
        $site->setEnabled($input->enabled);
        foreach ($input->networks as $network) {
            $site->addNetwork($network);
        }

        return $site;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a Site we transformed the data already
        if ($data instanceof Site) {
            return false;
        }
        return Site::class === $to && null !== ($context['input']['class'] ?? null);
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
            return new SiteInput();
        }
        // For a PUT or PATCH
        $siteData = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a SiteInput based on the current resource's data
        $dto = new SiteInput();

        $dto->code = $siteData->getCode();
        $dto->name = $siteData->getName();
        $dto->description = $siteData->getDescription();
        $dto->externalId = $siteData->getExternalId();
        $dto->userGroup = $siteData->getUserGroup();
        $dto->address = $siteData->getAddress();
        $dto->contact = $siteData->getContact();
        $dto->networks = $siteData->getNetworks();
        $dto->enabled = $siteData->isEnabled();
        $dto->templateModel = $siteData->getTemplateModel();

        return $dto;


    }
}
