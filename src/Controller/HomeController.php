<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;
use App\Repository\UserRepository;

class HomeController extends AbstractController
{
    #[Route('/base', name: 'show_base_template')]
    public function base(): Response
    {
        // Rediriger vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }

    #[Route('/', name: 'app_home')]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/avis', name: 'app_avis')]
    public function avis(AvisRepository $avisRepository): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll()
        ]);
    }

    #[Route('/adminhome', name: 'base_admin')]
    public function homeadmin(): Response
    {
        return $this->render('admin/homeadmin.html.twig');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(AvisRepository $avisRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        
        // Get user's reviews
        $userAvis = $avisRepository->findBy(['user' => $user]);
        
        // Get recent users (for admin view)
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        
        // Get recent reviews (for admin view)
        $recentReviews = $avisRepository->findBy([], ['createdAt' => 'DESC'], 5);
    
        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'userAvis' => $userAvis,
            'recentUsers' => $recentUsers,
            'recentReviews' => $recentReviews
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('website/about.html.twig');
    }

    #[Route('/posts', name: 'app_posts')]
    public function posts(): Response
    {
        return $this->render('posts.html.twig');
    }

    #[Route('/notfound', name: 'app_not_found')]
    public function not_found(): Response
    {
        return $this->render('website/notfound.html.twig');
    }
}