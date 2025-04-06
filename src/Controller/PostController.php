<?php

namespace App\Controller;
use App\Entity\Utilisateur;  

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PostController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/newpost', name: 'app_new_post', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
    
        
        $post = new Post();

        
        $post->setDateCreation(new \DateTime());
        $post->setLikes(0);
        $post->setDislikes(0);
// Récupérer l'utilisateur ayant id 1 depuis la base de données
$utilisateur = $entityManager->getRepository(Utilisateur::class)->find(1);
$post->setUtilisateur($utilisateur);

        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                
                // Déplacer le fichier
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
                
                // Mettre à jour l'entité
                $post->setImage($newFilename);
            }
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_posts');
        }
    
        return $this->render('ajouterPost.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/{id_post}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
       
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
              /** @var UploadedFile $imageFile */
              $imageFile = $form->get('imageFile')->getData();
            
              if ($imageFile) {
                  $newFilename = uniqid().'.'.$imageFile->guessExtension();
                  
                  // Déplacer le fichier
                  $imageFile->move(
                      $this->getParameter('upload_directory'),
                      $newFilename
                  );
                  
                  // Mettre à jour l'entité
                  $post->setImage($newFilename);
              }
            $entityManager->flush();

            $this->addFlash('success', 'Post modifié avec succès!');
            return $this->redirectToRoute('app_posts');
        }

        return $this->render('/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_post}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
      
        
        if ($this->isCsrfTokenValid('delete'.$post->getIdPost(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            
            $this->addFlash('success', 'Post supprimé avec succès!');
        }

        return $this->redirectToRoute('app_posts');
    }

    #[Route('/posts', name: 'app_posts')]
    public function posts(EntityManagerInterface $entityManager): Response
    {
        $postRepository = $entityManager->getRepository(Post::class);
        $posts = $postRepository->findAll();
        
        return $this->render('posts.html.twig', [
            'posts' => $posts,
    
        ]);
    }

}