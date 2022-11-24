<?php

namespace App\Validator;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TagConstraintValidator extends ConstraintValidator
{

    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }


    public function validate($value, Constraint $constraint)
    {
        // Entity class name with namespace
        $entityClassName = $this->context->getClassName();

        /* @var $constraint \App\Validator\TagConstraint */

        if (null === $value || '' === $value || [] === $value || ($value instanceof PersistentCollection && $value->count() < 1)) {
            return;
        }
        // TODO can't validate on required since if no Tags ArrayCollection we can't check the TagGroup
        // Tag options should be multiValueAllowed to allow user to add multiple tags to an entity
        if (($value instanceof ArrayCollection && !$value->isEmpty()) || ($value instanceof PersistentCollection && !$value->isEmpty())) {
            $tags = $value->toArray();

            foreach ($tags as $tag) {
                $tagGroup = $tag->getTagGroup();

                // Validate that the Target->entityClassName of the TagGroup matches the current entity class name
                $target = $tagGroup->getTarget();
                if ($target->getEntityClassName() !== $entityClassName) {
                    return $this->context->buildViolation($constraint->targetErrorMessage)
                        ->addViolation();
                }
                $tagsCount = count(array_filter($tags, static function ($filterableTag) use ($tagGroup) {
                    return $filterableTag->getTagGroup()->getCode() === $tagGroup->getCode();
                }));
                // Get the options
                $options = $tagGroup->getOptions();

                // Default value is true
                $allowMultiValue = array_key_exists('multiValueAllowed',
                    $options) ? $options['multiValueAllowed'] === true : true;
                // Default value is false
                $required = array_key_exists('required',
                    $options) ? $options['required'] === true : false;

                if (($allowMultiValue && $tagsCount >= 1) || ($tagsCount < 2 && !$allowMultiValue)) {
                    return;
                }

                if ($required && $tagsCount > 1) {
                    return;
                }


            }
            return $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

        if ($value instanceof ArrayCollection && $value->isEmpty()) {
            return;
        }
        return $this->context->buildViolation($constraint->message)
            ->addViolation();

    }
}
