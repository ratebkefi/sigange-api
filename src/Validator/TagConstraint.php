<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

 // @Target({"PROPERTY", "ANNOTATION"})
/**
 * @Annotation
 */
class TagConstraint extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    // TODO change the message
    public $message = 'The options of the TagGroup prevents the tag to be valid';
    public $targetErrorMessage = 'The target is not valid';
}
