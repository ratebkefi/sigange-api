<?php


namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\ScreenInput;
use App\Entity\Screen;
use App\Service\TagGroupHandler;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class ScreenDataTransformer implements DataTransformerInitializerInterface
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
        $screen = $isPopulated ? $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] : new Screen();

        try {
            // @var Screen
            $screen = $this->tagGroupHandler->handleTagGroups($input, $screen);

        } catch (Exception $exception) {
            $message = $exception->getMessage();
            throw new BadRequestException("Bad request: $message");
        }

        $screen->setCode($input->code);
        $screen->setName($input->name);
        $screen->setDescription($input->description);
        $screen->setDeviceOutput($input->deviceOutput);
        $screen->setSite($input->site);
        $screen->setEnabled($input->enabled);

        return $screen;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a Screen we transformed the data already
        if ($data instanceof Screen) {
            return false;
        }
        return Screen::class === $to && null !== ($context['input']['class'] ?? null);
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
            return new ScreenInput();
        }
        // For a PUT or PATCH
        $screenData = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        // Initialize a ScreenInput based on the current resource's data
        $dto = new ScreenInput();

        $dto->code = $screenData->getCode();
        $dto->name = $screenData->getName();
        $dto->description = $screenData->getDescription();
        $dto->deviceOutput = $screenData->getDeviceOutput();
        $dto->site = $screenData->getSite();
        $dto->enabled = $screenData->isEnabled();

        return $dto;


    }
}
