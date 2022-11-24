<?php

namespace App\Form\DataTransformer;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IRIToUserTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     */
    public function transform($value)
    {

        if (null === $value) {
            return '';
        }
        if (!$value instanceof User) {
            return '';
        }

        return $value;
    }

    /**
     * Transforms a string IRI to an entity.
     *
     * @param $value
     * @return mixed|void
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return;
        }
        // Allow to use either IRI (api/users/CODE) or code directly
        $found = preg_match('/(?:(api\/users\/)?)([0-9a-f-]+)/', $value, $matches);
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['code' => $matches[2]]);

        if (null === $user) {
            throw new TransformationFailedException(sprintf(
                'A User with IRI "%s" does not exist!',
                $value
            ));
        }

        return $user;
    }


}
