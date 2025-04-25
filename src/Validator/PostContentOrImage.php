<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PostContentOrImage extends Constraint
{
    public string $message = 'Vous devez écrire un contenu ou ajouter une image.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
