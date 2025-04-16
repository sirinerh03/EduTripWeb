<?php

namespace App\Controller;
use App\Entity\Utilisateur;  
use App\Entity\Commentaire; 
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{
    
    #[Route('/admin_posts', name: 'admin_posts')]
    public function posts(EntityManagerInterface $entityManager): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);
        $posts = $postRepository->findAll();
        
        foreach ($posts as $post) {
            $post->getCommentaires();  // Cette ligne est nécessaire pour éviter un problème de lazy loading
        }
        return $this->render('admin/adminposts.html.twig', [
            'posts' => $posts,
    
        ]);
    }
}