<?php

namespace App\Controller;

use App\Repository\UniversityRepository;
use App\Repository\CandidatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/statistics')]
class StatisticsController extends AbstractController
{
    
    #[Route('/', name: 'app_statistics')]
    public function index(
        UniversityRepository $universityRepository,
        CandidatureRepository $candidatureRepository
    ): Response {
        // Get statistics using repositories
        $statistics = [
            'totalUniversities' => $universityRepository->getTotalUniversities(),
            'totalCandidatures' => $candidatureRepository->getTotalCandidatures(),
            'statusStats' => $candidatureRepository->getCandidaturesByStatus(),
            'cityStats' => $universityRepository->getUniversitiesByCity(),
            'universityStats' => $universityRepository->getCandidaturesPerUniversity(),
        ];

        return $this->render('statistics/index.html.twig', $statistics);
    }
}