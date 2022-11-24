<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AdminConstraint extends Constraint
{

    public string $invalidRolesMessage = 'Invalid userRoles.';
    public string $invalidGroupsMessage = 'Invalid groups.';

}
