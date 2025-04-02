<?php
namespace App\Controller;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;
class HomeController extends AbstractController
{




    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    
    #[Route('/base', name: 'show_base_template')]
    public function base(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('website/about.html.twig');
    }

    /*#[Route('/posts', name: 'app_posts')]
    public function posts(): Response
    {
        return $this->render('posts.html.twig');
    }*/


    #[Route('/posts', name: 'app_posts')]
    public function posts(EntityManagerInterface $entityManager): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);
        $posts = $postRepository->findAll();
        
        return $this->render('posts.html.twig', [
            'posts' => $posts,
    
        ]);
    }








    #[Route('/team', name: 'app_team')]
    public function team(): Response
    {
        return $this->render('website/team.html.twig');
    }
    
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('website/contact.html.twig');
    }
    
    #[Route('/testimonial', name: 'app_testimonial')]
    public function testimonial(): Response
    {
        return $this->render('website/testimonial.html.twig');
    }

    #[Route('/notfound', name: 'app_not_found')]
    public function not_found(): Response
    {
        return $this->render('website/notfound.html.twig');
    }
   
}