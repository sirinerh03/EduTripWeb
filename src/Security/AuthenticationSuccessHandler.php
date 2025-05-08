<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

   public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
{
    $user = $token->getUser();

    if (!method_exists($user, 'getRoles')) {
        throw new \LogicException('User object must implement getRoles() method.');
    }

    $roles = $user->getRoles();

    if (in_array('ROLE_ADMIN', $roles, true)) {
        return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
    }

    // ROLE_AGENCY or others go to the same route
    return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
}

} 