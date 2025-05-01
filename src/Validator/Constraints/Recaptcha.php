<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Recaptcha extends Constraint
{
    public $message = 'La vérification reCAPTCHA a échoué. Veuillez réessayer.';

    public function validatedBy()
    {
        return RecaptchaValidator::class;
    }
}
