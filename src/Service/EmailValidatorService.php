<?php
namespace App\Service;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailValidatorService
{
    private $emailValidator;
    private $params;
    private $httpClient;
    private $useApi;

    public function __construct(
        ParameterBagInterface $params = null,
        HttpClientInterface $httpClient = null
    ) {
        $this->emailValidator = new EmailValidator();
        $this->params = $params;
        $this->httpClient = $httpClient;
        $this->useApi = ($params !== null && $httpClient !== null);
    }

    /**
     * Valide le format de l'email selon les normes RFC
     */
    public function isValidFormat(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        return $this->emailValidator->isValid($email, new RFCValidation());
    }

    /**
     * Valide le format de l'email et vérifie les enregistrements DNS/MX du domaine
     */
    public function isValidWithDNSCheck(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        $validators = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);

        return $this->emailValidator->isValid($email, $validators);
    }

    /**
     * Vérifie la validité d'une adresse email via l'API AbstractAPI
     */
    public function validateWithApi(?string $email): array
    {
        if (empty($email)) {
            return [
                'is_valid_format' => false,
                'deliverability' => 'UNDELIVERABLE',
                'quality_score' => 0,
                'is_disposable_email' => false
            ];
        }

        if (!$this->useApi) {
            return [
                'is_valid_format' => $this->isValidFormat($email),
                'deliverability' => $this->isValidWithDNSCheck($email) ? 'DELIVERABLE' : 'UNDELIVERABLE',
                'quality_score' => 0.7,
                'is_disposable_email' => false
            ];
        }

        $apiKey = $this->params->get('abstract_api.email_validation_key');
        $apiUrl = 'https://emailvalidation.abstractapi.com/v1/';

        try {
            $response = $this->httpClient->request('GET', $apiUrl, [
                'query' => [
                    'api_key' => $apiKey,
                    'email' => $email
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            // En cas d'erreur, on utilise la validation locale
            return [
                'is_valid_format' => $this->isValidFormat($email),
                'deliverability' => $this->isValidWithDNSCheck($email) ? 'DELIVERABLE' : 'UNDELIVERABLE',
                'quality_score' => 0.7,
                'is_disposable_email' => false
            ];
        }
    }

    /**
     * Vérifie si l'email est valide selon les critères définis
     * Utilise l'API si disponible, sinon utilise la validation locale
     */
    public function isValid(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        if ($this->useApi) {
            $validation = $this->validateWithApi($email);

            // Vérifier si l'email est au format valide
            if (!$validation['is_valid_format']) {
                return false;
            }

            // Vérifier si l'email est livrable
            if ($validation['deliverability'] === 'UNDELIVERABLE') {
                return false;
            }

            // Vérifier si l'email est jetable
            if ($validation['is_disposable_email']) {
                return false;
            }

            // Vérifier le score de qualité (optionnel)
            if (isset($validation['quality_score']) && $validation['quality_score'] < 0.5) {
                return false;
            }

            return true;
        } else {
            // Utiliser la validation locale
            return $this->isValidFormat($email) && $this->isValidWithDNSCheck($email);
        }
    }

    /**
     * Vérifie si l'email est valide et retourne un message d'erreur si ce n'est pas le cas
     *
     * @return array [bool $isValid, string|null $errorMessage]
     */
    public function validateEmail(?string $email): array
    {
        if (empty($email)) {
            return [false, 'L\'adresse email ne peut pas être vide.'];
        }

        if (!$this->isValidFormat($email)) {
            return [false, 'Le format de l\'adresse email n\'est pas valide.'];
        }

        if ($this->useApi) {
            $validation = $this->validateWithApi($email);

            if ($validation['deliverability'] === 'UNDELIVERABLE') {
                return [false, 'Le domaine de l\'adresse email n\'est pas valide ou n\'accepte pas d\'emails.'];
            }

            if ($validation['is_disposable_email']) {
                return [false, 'Les adresses email temporaires ne sont pas autorisées.'];
            }

            if (isset($validation['quality_score']) && $validation['quality_score'] < 0.5) {
                return [false, 'L\'adresse email semble être de faible qualité.'];
            }

            return [true, null];
        } else {
            // Utiliser la validation locale
            if (!$this->isValidWithDNSCheck($email)) {
                return [false, 'Le domaine de l\'adresse email n\'est pas valide ou n\'accepte pas d\'emails.'];
            }

            return [true, null];
        }
    }
}
