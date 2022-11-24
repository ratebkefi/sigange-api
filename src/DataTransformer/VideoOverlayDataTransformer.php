<?php


namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\VideoOverlayInput;
use App\Entity\VideoOverlay;
use App\Service\TagGroupHandler;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class VideoOverlayDataTransformer implements DataTransformerInitializerInterface
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
        $videoOverlay = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new VideoOverlay();

        try {
            // @var VideoOverlay
            $videoOverlay = $this->tagGroupHandler->handleTagGroups($input, $videoOverlay);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }

        $videoOverlay->setEnabled($input->enabled);
        $videoOverlay->setName($input->name);
        $videoOverlay->setCode($input->code);
        $videoOverlay->setUrl($input->url);
        $videoOverlay->setUserGroup($input->userGroup);

        return $videoOverlay;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a VideoOverlay we transformed the data already
        if ($data instanceof VideoOverlay) {
            return false;
        }
        return VideoOverlay::class === $to && null !== ($context['input']['class'] ?? null);
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
            return new VideoOverlayInput();
        }
        // For a PUT or PATCH
        $videoOverlayData = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a VideoOverlayInput based on the current resource's data
        $dto = new VideoOverlayInput();

        $dto->enabled = $videoOverlayData->isEnabled();
        $dto->name = $videoOverlayData->getName();
        $dto->code = $videoOverlayData->getCode();
        $dto->url = $videoOverlayData->getUrl();
        $dto->userGroup = $videoOverlayData->getUserGroup();

        return $dto;


    }
}
