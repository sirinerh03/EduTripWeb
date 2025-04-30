<?php
namespace App\Validator;

use App\Entity\Post;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PostContentOrImageValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof PostContentOrImage) {
            throw new UnexpectedTypeException($constraint, PostContentOrImage::class);
        }

        if (!$object instanceof Post) {
            return;
        }

        $contenu = $object->getContenu();
        $imageFile = $object->getImageFile();

        if (empty($contenu) && $imageFile === null) {
            $this->context->buildViolation($constraint->message)
                ->atPath('contenu') // ou imageFile, mais au moins un champ
                ->addViolation();
        }
    }
}
