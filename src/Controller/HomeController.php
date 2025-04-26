<?php
namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function base(): Response
    {
        // Rediriger vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }

    #[Route('/', name: 'app_home')]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('home/index.html.twig');
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

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(AvisRepository $avisRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer les avis de l'utilisateur
        $userAvis = $avisRepository->findBy(['user' => $user]);
        
        // Use dashboard.html.twig which extends base.html.twig with user dashboard content
        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'userAvis' => $userAvis
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('website/about.html.twig');
    }

    #[Route('/universities', name: 'app_universities')]
    public function universities(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('info', 'Veuillez vous connecter pour accéder à la liste des universités.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('website/universities.html.twig');
    }

    #[Route('/housing', name: 'app_housing')]
    public function housing(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('info', 'Veuillez vous connecter pour accéder aux offres d\'hébergement.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('website/housing.html.twig');
    }

    #[Route('/agencies', name: 'app_agencies')]
    public function agencies(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('info', 'Veuillez vous connecter pour accéder à la liste des agences.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('website/agencies.html.twig');
    }

    #[Route('/flights', name: 'app_flights')]
    public function flights(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('info', 'Veuillez vous connecter pour accéder aux offres de vols.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('website/flights.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('website/contact.html.twig');
    }

    #[Route('/notfound', name: 'app_not_found')]
    public function not_found(): Response
    {
        return $this->render('website/notfound.html.twig');
    }
}