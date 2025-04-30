<?php
// Standalone script to create admin user for EduTrip
// Place this script in the project root and run with: php create_admin.php

require_once __DIR__.'/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

// Boot the Symfony kernel
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

// Get services from the container
$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.user_password_hasher');

// Admin user details
$email = 'admin@edutrip.com';
$password = 'Admin123!';
$prenom = 'Admin';
$nom = 'EduTrip';
$tel = '12345678';

// Check if admin already exists
$existingUser = $entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
if ($existingUser) {
    echo "An admin with this email already exists.\n";
    exit(1);
}

// Create the admin user
$user = new User();
$user->setMail($email);
$user->setPrenom($prenom);
$user->setNom($nom);
$user->setTel($tel);
$user->setStatus('active');
$user->setRole('ROLE_ADMIN');

// Hash the password
$hashedPassword = $passwordHasher->hashPassword($user, $password);
$user->setPassword($hashedPassword);

// Save to database
$entityManager->persist($user);
$entityManager->flush();

echo "Admin user created successfully!\n";
echo "Email: $email\n";
echo "Password: $password\n"; 