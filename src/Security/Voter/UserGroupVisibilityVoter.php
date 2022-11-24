<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Interfaces\UserGroupVisibilityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class UserGroupVisibilityVoter
 *
 * Restrict access to operations for some entities.
 * For these entities, they should share a UserGroup with the connected user, or the access will be denied to them.
 *
 * @package App\Security\Voter
 */
class UserGroupVisibilityVoter extends Voter
{

    protected function supports(string $attribute, $subject): bool
    {
        // NOTE: abstain by default for all COLLECTION GET roles since no subject
        return $subject instanceof UserGroupVisibilityInterface;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /**
         * @var User
         */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }
        // Grant access to Super Admin
        if ($user->isSuperAdmin()) {
            return true;
        }
        // Check if the entity share a UserGroup with the User
        $userGroups = $user->getGroups()->toArray();
        $groupsInEntity = $subject->getUserGroups()->getValues();

        $hasUserUserGroup = count(array_intersect($groupsInEntity, $userGroups)) > 0;

        if ($hasUserUserGroup) {
            return true;
        }

        return false;

    }

}
