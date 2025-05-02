<?php
// Script pour tester la connexion admin

// Charger l'autoloader de Composer
require __DIR__.'/vendor/autoload.php';

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

// Créer un kernel Symfony
$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer les services nécessaires
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.user_password_hasher');

// Récupérer l'utilisateur admin
$userRepository = $entityManager->getRepository(User::class);
$admin = $userRepository->findOneBy(['mail' => 'admin@edutrip.com']);

if (!$admin) {
    echo "L'utilisateur admin n'existe pas dans la base de données.\n";
    
    // Créer l'utilisateur admin
    echo "Création de l'utilisateur admin...\n";
    $admin = new User();
    $admin->setMail('admin@edutrip.com');
    $admin->setPrenom('Admin');
    $admin->setNom('EduTrip');
    $admin->setTel('12345678');
    $admin->setStatus('active');
    $admin->setRole('ROLE_ADMIN');
    
    // Hasher le mot de passe
    $hashedPassword = $passwordHasher->hashPassword($admin, 'Admin123!');
    $admin->setPassword($hashedPassword);
    
    // Sauvegarder dans la base de données
    $entityManager->persist($admin);
    $entityManager->flush();
    
    echo "Utilisateur admin créé avec succès!\n";
} else {
    echo "L'utilisateur admin existe dans la base de données.\n";
    
    // Vérifier le mot de passe
    $plainPassword = 'Admin123!';
    $isPasswordValid = $passwordHasher->isPasswordValid($admin, $plainPassword);
    
    echo "Mot de passe valide : " . ($isPasswordValid ? "Oui" : "Non") . "\n";
    
    if (!$isPasswordValid) {
        echo "Mise à jour du mot de passe...\n";
        $hashedPassword = $passwordHasher->hashPassword($admin, $plainPassword);
        $admin->setPassword($hashedPassword);
        $entityManager->flush();
        echo "Mot de passe mis à jour avec succès!\n";
    }
}

echo "\nInformations sur l'utilisateur admin :\n";
echo "Email : " . $admin->getMail() . "\n";
echo "Rôle : " . $admin->getRole() . "\n";
echo "Statut : " . $admin->getStatus() . "\n";
echo "Prénom : " . $admin->getPrenom() . "\n";
echo "Nom : " . $admin->getNom() . "\n";
