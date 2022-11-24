<?php


namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\VideoStreamInput;
use App\Entity\VideoStream;
use App\Service\TagGroupHandler;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Exception;

final class VideoStreamDataTransformer implements DataTransformerInitializerInterface
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
        $videoStream = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new VideoStream();

        try {
            // @var VideoStream
            $videoStream = $this->tagGroupHandler->handleTagGroups($input, $videoStream);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }

        $videoStream->setEnabled($input->enabled);
        $videoStream->setName($input->name);
        $videoStream->setCode($input->code);
        $videoStream->setUrl($input->url);
        $videoStream->setUserGroup($input->userGroup);

        return $videoStream;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a VideoStream we transformed the data already
        if ($data instanceof VideoStream) {
            return false;
        }
        return VideoStream::class === $to && null !== ($context['input']['class'] ?? null);
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
            return new VideoStreamInput();
        }
        // For a PUT or PATCH
        $videoStreamData =  $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a VideoStreamInput based on the current resource's data
        $dto = new VideoStreamInput();

        $dto->enabled = $videoStreamData->isEnabled();
        $dto->name = $videoStreamData->getName();
        $dto->code = $videoStreamData->getCode();
        $dto->url = $videoStreamData->getUrl();
        $dto->userGroup = $videoStreamData->getUserGroup();

        return $dto;

    }
}
