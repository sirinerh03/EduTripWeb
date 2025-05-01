<?php

namespace App\Security;

use App\Entity\User;
use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $recaptchaService;
    private $emailValidator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        RecaptchaService $recaptchaService,
        \App\Service\EmailValidatorService $emailValidator
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->recaptchaService = $recaptchaService;
        $this->emailValidator = $emailValidator;
    }

    public function authenticate(Request $request): Passport
    {
        // Récupérer les valeurs directement
        $mail = $request->request->get('mail', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Vérifier si l'email est valide avec notre service de validation avancé
        [$isValidEmail, $emailErrorMessage] = $this->emailValidator->validateEmail($mail);

        if (!$isValidEmail) {
            throw new CustomUserMessageAuthenticationException($emailErrorMessage);
        }

        // Vérifier le reCAPTCHA
        $recaptchaResponse = $request->request->get('g-recaptcha-response', '');

        // Vérifier que la réponse reCAPTCHA n'est pas vide
        if (empty($recaptchaResponse)) {
            throw new CustomUserMessageAuthenticationException('Veuillez valider le captcha.');
        }

        // Vérifier que la réponse reCAPTCHA est valide
        if (!$this->recaptchaService->verify($recaptchaResponse, $request->getClientIp())) {
            throw new CustomUserMessageAuthenticationException('La vérification reCAPTCHA a échoué. Veuillez réessayer.');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $mail);

        return new Passport(
            new UserBadge($mail),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Rediriger en fonction du rôle
        if ($user instanceof User) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
            }
        }

        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
