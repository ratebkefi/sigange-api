<?php

namespace App\Validator;

use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Ensure that a parent is present for a User that is not admin
 * Class ParentConstraintValidator
 * @package App\Validator
 */
class ParentConstraintValidator extends ConstraintValidator
{
    private Security $security;
    protected RequestStack $requestStack;
    protected EntityManagerInterface $entityManager;


    public function __construct(Security $security, RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;

    }

    public function validate($value, Constraint $constraint)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }
        $requestData = $request->toArray();
        $requestFields = array_keys($requestData);
        $propertyName = $this->context->getPropertyName();

        // Only set the constraint for properties userRoles and groups
        if ($propertyName !== 'parent') {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->context->buildViolation($constraint->anonymousMessage)
                ->addViolation();
            return;
        }
        $roles = $user->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles) || $user->isSuperAdmin();
        // Don't add the constraint for admins
        if ($isAdmin) {
            return;
        }
        $tempRoot = $this->context->getRoot();
        if (!$tempRoot instanceof UserGroup) {
            throw new \InvalidArgumentException('@ParentConstraint should be used only for the UserGroup entity');
        }

        // Ensure that parent (which is the only property tested) is present for a user that is not admin
        if ($value === null || $value === '') {
            $this->context->buildViolation($constraint->invalidMessage)
                ->addViolation();
        }


    }
}
