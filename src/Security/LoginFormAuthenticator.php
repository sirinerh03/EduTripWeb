<?php

namespace App\Security;

use App\Entity\User;
use App\Service\CaptchaService;
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
    private $captchaService;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CaptchaService $captchaService)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->captchaService = $captchaService;
    }

    public function authenticate(Request $request): Passport
    {
        // Récupérer les valeurs directement
        $mail = $request->request->get('mail', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Vérifier si l'email est valide avec notre service de validation avancé
        $emailValidator = new \App\Service\EmailValidatorService();
        [$isValidEmail, $emailErrorMessage] = $emailValidator->validateEmail($mail);

        if (!$isValidEmail) {
            throw new CustomUserMessageAuthenticationException($emailErrorMessage);
        }

        // Vérifier le captcha
        $captchaCode = $request->request->get('captcha', '');

        // Vérifier que le code captcha n'est pas vide
        if (empty($captchaCode)) {
            throw new CustomUserMessageAuthenticationException('Veuillez saisir le code captcha.');
        }

        // Vérifier que le code captcha est correct
        if (!$this->captchaService->validateCaptchaCode($captchaCode)) {
            throw new CustomUserMessageAuthenticationException('Le code captcha est incorrect.');
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
