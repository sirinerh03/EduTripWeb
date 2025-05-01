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
    name: 'app:update-admin-password',
    description: 'Updates the admin user password',
)]
class UpdateAdminPasswordCommand extends Command
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
        $newPassword = 'Admin123!';
        
        // Find admin user
        $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        if (!$adminUser) {
            $io->error('Admin user not found');
            return Command::FAILURE;
        }

        // Update password
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $newPassword);
        $adminUser->setPassword($hashedPassword);

        // Save to database
        $this->entityManager->flush();

        $io->success(sprintf('Admin password updated for: %s', $email));
        $io->note(sprintf('New password: %s', $newPassword));

        return Command::SUCCESS;
    }
} 