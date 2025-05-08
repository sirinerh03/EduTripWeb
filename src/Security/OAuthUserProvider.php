<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $email = $response->getEmail();
        
        // Vérifier si l'utilisateur existe déjà
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        
        // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
        if (null === $user) {
            $user = new User();
            $user->setMail($email);
            $user->setNom($response->getLastName() ?? 'Utilisateur');
            $user->setPrenom($response->getFirstName() ?? 'OAuth');
            $user->setPassword(''); // Mot de passe vide car authentification OAuth
            $user->setRole('ROLE_USER');
            $user->setStatus('active');
            $user->setIsVerified(true); // L'utilisateur est vérifié car authentifié par OAuth
            $user->setTel('00000000'); // Valeur par défaut
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
    
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }
    

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
