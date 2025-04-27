<?php

namespace App\Service;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class EmailValidatorService
{
    private $emailValidator;

    public function __construct()
    {
        $this->emailValidator = new EmailValidator();
    }

    /**
     * Valide le format de l'email selon les normes RFC
     */
    public function isValidFormat(string $email): bool
    {
        return $this->emailValidator->isValid($email, new RFCValidation());
    }

    /**
     * Valide le format de l'email et vérifie les enregistrements DNS/MX du domaine
     */
    public function isValidWithDNSCheck(string $email): bool
    {
        $validators = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);

        return $this->emailValidator->isValid($email, $validators);
    }

    /**
     * Vérifie si l'email est valide et retourne un message d'erreur si ce n'est pas le cas
     * 
     * @return array [bool $isValid, string|null $errorMessage]
     */
    public function validateEmail(string $email): array
    {
        if (empty($email)) {
            return [false, 'L\'adresse email ne peut pas être vide.'];
        }

        if (!$this->isValidFormat($email)) {
            return [false, 'Le format de l\'adresse email n\'est pas valide.'];
        }

        if (!$this->isValidWithDNSCheck($email)) {
            return [false, 'Le domaine de l\'adresse email n\'est pas valide ou n\'accepte pas d\'emails.'];
        }

        return [true, null];
    }
}
