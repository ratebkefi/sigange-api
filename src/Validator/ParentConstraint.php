<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ParentConstraint extends Constraint
{

    public string $invalidMessage = 'Parent is mandatory.';

}
