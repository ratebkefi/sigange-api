<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class CustomRoleVoter
 *
 * Decorates the Symfony RoleVoter class to allow access to super admin
 *
 * @package App\Security\Voter
 */
class CustomRoleVoter extends RoleVoter
{
    private string $prefix;

    public function __construct(string $prefix = 'ROLE_')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        $roles = $this->extractRoles($token);

        $user = $token->getUser();

        // Grant access to Super Admin
        if ($user instanceof User && $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }
        foreach ($attributes as $attribute) {
            if (!\is_string($attribute) || 0 !== strpos($attribute, $this->prefix)) {
                continue;
            }

            if ('ROLE_PREVIOUS_ADMIN' === $attribute) {
                trigger_deprecation('symfony/security-core', '5.1',
                    'The ROLE_PREVIOUS_ADMIN role is deprecated and will be removed in version 6.0, use the IS_IMPERSONATOR attribute instead.');
            }

            $result = VoterInterface::ACCESS_DENIED;

            // Check the User has the right role for this entity
            foreach ($roles as $role) {
                if ($attribute === $role) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

        }

        return $result;
    }
}
