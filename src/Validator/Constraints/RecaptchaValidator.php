<?php

namespace App\Validator\Constraints;

use App\Service\RecaptchaService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RecaptchaValidator extends ConstraintValidator
{
    private $recaptchaService;
    private $requestStack;

    public function __construct(RecaptchaService $recaptchaService, RequestStack $requestStack)
    {
        $this->recaptchaService = $recaptchaService;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Recaptcha) {
            throw new UnexpectedTypeException($constraint, Recaptcha::class);
        }

        $request = $this->requestStack->getCurrentRequest();

        // Récupérer la réponse reCAPTCHA
        // Essayer d'abord de récupérer depuis la requête directe, puis depuis le formulaire
        $recaptchaResponse = $request->request->get('g-recaptcha-response') ?: $value;

        if (empty($recaptchaResponse)) {
            $this->context->buildViolation('Veuillez valider le captcha.')
                ->addViolation();
            return;
        }

        if (!$this->recaptchaService->verify($recaptchaResponse, $request->getClientIp())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
