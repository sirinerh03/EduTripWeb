<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository, ReviewRepository $reviewRepository): Response
    {
        // Get total counts
        $totalUsers = $userRepository->count([]);
        $totalReviews = $reviewRepository->count([]);

        // Get recent users and reviews (limit to 5)
        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentReviews = $reviewRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalReviews' => $totalReviews,
            'recentUsers' => $recentUsers,
            'recentReviews' => $recentReviews,
        ]);
    }
} 