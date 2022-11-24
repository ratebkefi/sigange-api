<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\TagGroup;
use App\Interfaces\TaggableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TagGroupHandler
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Checks the tags data satisfy the constraints (relative to the tag groups) before assigning each tag to the entity.
     * @param object $input
     * @param TaggableInterface $entity
     * @return TaggableInterface
     */
    public function handleTagGroups(object $input, TaggableInterface $entity): TaggableInterface
    {
        $matches = [];
        $tagsData = $input->tagsData;
        if (!$tagsData) {
            return $entity;
        }
        $required = false;
        $tagsResult = [];

        // To handle the case when a Tag is removed, we remove all tags of the entity before adding them again
        $entity->removeAllTags();
        foreach ($tagsData as $tagGroupCode => $tags) {
            $found = preg_match('/(?:(api\/tag_groups\/)?)([0-9a-f-]+)/', $tagGroupCode, $matches);

            try {

                $tagGroup = $this->entityManager
                    ->getRepository(TagGroup::class)
                    ->findOneBy(['code' => $matches[2]]);
                if ($tagGroup) {
                    $options = $tagGroup->getOptions();
                    // Check the required options, the other options are checked by the TagConstraint
                    $required = array_key_exists('required',
                        $options) ? $options['required'] === true : false;

                } else {
                    throw new BadRequestException('Tag Group is required');
                }
                // When multiple false, only one tag will be sent
                if (is_iterable($tags)) {
                    foreach ($tags as $tagIRI) {
                        $found = preg_match('/(?:(api\/tags\/)?)([0-9a-f-]+)/', $tagIRI, $matches);
                        if ($found) {
                            $tag = $this->entityManager
                                ->getRepository(Tag::class)
                                ->findOneBy(['code' => $matches[2]]);
                            $tagsResult[] = $tag;
                            if ($tag) {
                                $entity->addTag($tag);
                            }
                        }
                    }
                } else {
                    $found = preg_match('/(?:(api\/tags\/)?)([0-9a-f-]+)/', $tags, $matches);
                    if ($found) {
                        $tag = $this->entityManager
                            ->getRepository(Tag::class)
                            ->findOneBy(['code' => $matches[2]]);
                        $tagsResult[] = $tag;
                        if ($tag) {
                            $entity->addTag($tag);
                        }
                    }
                }


            } catch (\Exception $exception) {
                throw new BadRequestException('Error on format of tagsData' . $exception->getMessage());
            }
        }
        if ($required && count($tagsResult) < 1) {
            throw new BadRequestException('At least one tag is required');
        }
        return $entity;
    }
}
