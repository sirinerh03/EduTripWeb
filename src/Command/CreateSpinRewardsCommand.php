<?php

namespace App\Command;

use App\Entity\SpinReward;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-spin-rewards',
    description: 'Crée les récompenses de spin pour les avis',
)]
class CreateSpinRewardsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Définir les récompenses à créer
        $rewards = [
            [
                'percentage' => 10,
                'description' => 'Félicitations ! Vous avez gagné une réduction de 10% sur votre prochain voyage éducatif avec EduTrip.'
            ],
            [
                'percentage' => 20,
                'description' => 'Félicitations ! Vous avez gagné une réduction de 20% sur votre prochain voyage éducatif avec EduTrip.'
            ],
            [
                'percentage' => 30,
                'description' => 'Félicitations ! Vous avez gagné une réduction de 30% sur votre prochain voyage éducatif avec EduTrip.'
            ]
        ];

        $createdCount = 0;

        foreach ($rewards as $rewardData) {
            // Vérifier si la récompense existe déjà
            $existingReward = $this->entityManager->getRepository(SpinReward::class)->findOneBy([
                'percentage' => $rewardData['percentage']
            ]);

            if (!$existingReward) {
                $reward = new SpinReward();
                $reward->setPercentage($rewardData['percentage']);
                $reward->setDescription($rewardData['description']);
                $reward->setIsActive(true);

                $this->entityManager->persist($reward);
                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d récompenses ont été créées avec succès.', $createdCount));
        } else {
            $io->info('Toutes les récompenses existent déjà.');
        }

        return Command::SUCCESS;
    }
}
