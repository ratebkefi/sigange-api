<?php

namespace App\Validator;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AdminConstraintValidator extends ConstraintValidator
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

        /* @var $constraint AdminConstraint */

        if (null === $value || '' === $value) {
            return;
        }
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $this->context->buildViolation($constraint->anonymousMessage)
                ->addViolation();
            return;
        }
        $tempRoot = $this->context->getRoot();
        // Note: Form is the context when using UserPasswordController to patch the User's password
        if (!$tempRoot instanceof User && !$tempRoot instanceof Form) {
            throw new \InvalidArgumentException('@AdminConstraint should be used only for the User entity');
        }

        // Only set the constraint for queries that contains 'userRoles' or 'groups' property
        if (count(array_intersect($requestFields, ['userRoles', 'groups'])) <= 0) {
            return;
        }

        // Only set the constraint for properties userRoles and groups
        if (!in_array($propertyName, ['userRoles', 'groups'])) {
            return;
        }

        $roles = $user->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles) || $user->isSuperAdmin();
        // Don't add the constraint for admins
        if ($isAdmin) {
            return;
        }

        $requestedRoles = $requestData['userRoles'];
        // Check that the user doesn't add ROLE_ADMIN or ROLE_SUPER_ADMIN

        $requestedRoleCodes = [];
        foreach ($requestedRoles as $requestedRole) {
            // Allow to use either IRI (api/users/CODE) or code directly
            $foundCode = preg_match('/(?:(api\/user_roles\/)?)([0-9a-f-]+)/', $requestedRole, $matches);
            if ($foundCode) {
                $requestedRoleCodes[] = $matches[2];
            }
        }

        if (count($requestedRoleCodes) > 0) {
            $qb = $this->entityManager->createQueryBuilder();
            try {
                // @var array
                $foundRequestRoles = $qb
                    ->select('r')
                    ->from('App:UserRole', 'r')
                    ->where('r.code IN (:ids)')
                    ->setParameter('ids', array_map(static function ($id) {
                        return Uuid::fromString($id)->toBinary();
                    }, $requestedRoleCodes))
                    ->getQuery()
                    ->getResult();

                foreach ($foundRequestRoles as $foundRole) {
                    // Simple User can't add an admin role to another User
                    if ($foundRole && str_contains($foundRole->getRoleName(), 'ADMIN')) {
                        $this->context->buildViolation($constraint->invalidRolesMessage)
                            ->addViolation();
                    }
                }
            } catch (Exception $exception) {
                $message = $exception->getMessage();
                throw new BadRequestException("User Role error: $message");
            }
        }
    }
}
