<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates an admin user',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Admin user details
        $email = 'admin@edutrip.com';
        $password = 'Admin123!';
        $prenom = 'Admin';
        $nom = 'EduTrip';
        $tel = '12345678';
        
        // Check if admin already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        if ($existingUser) {
            $io->error('An admin with this email already exists.');
            return Command::FAILURE;
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
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Save to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user created: %s', $email));
        $io->note(sprintf('Password: %s', $password));

        return Command::SUCCESS;
    }
} 