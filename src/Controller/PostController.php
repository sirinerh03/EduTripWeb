<?php

namespace App\Controller;
use App\Entity\Utilisateur;  
use App\Entity\Commentaire; 
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Post;
use App\Form\PostType;
use App\Form\CommentaireType;
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
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;

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
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        foreach ($posts as $post) {
            $post->getCommentaires();  // Cette ligne est nécessaire pour éviter un problème de lazy loading
        }
        return $this->render('posts.html.twig', [
            'posts' => $posts,
            'form' => $form->createView(),
    
        ]);
    }


    #[Route('/commentaire/add', name: 'ajouter_commentaire', methods: ['GET','POST'])]
    public function ajouterCommentaire(Request $request, EntityManagerInterface $em, PostRepository $postRepository): JsonResponse
    {
        $postId = $request->request->get('postId');
        $contenu = $request->request->get('contenu');
    
        if (!$postId || !$contenu) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }
    
        $post = $postRepository->find($postId);
        if (!$post) {
            return new JsonResponse(['error' => 'Post introuvable'], 404);
        }
    
        $commentaire = new Commentaire();
        $commentaire->setContenu($contenu);
        $commentaire->setPost($post);
        $commentaire->setDateCommentaire(new \DateTime());
        
        // Optionnel : récupérer l’utilisateur connecté
        // $commentaire->setUtilisateur($this->getUser());
        $utilisateur = $em->getRepository(Utilisateur::class)->find(1);
        $commentaire->setUtilisateur($utilisateur);
        $em->persist($commentaire);
        $em->flush();
    
        return new JsonResponse(['contenu' => $commentaire->getContenu()]);
    }
    
  
    
    #[Route('/commentaire/{id}/delete', name: 'delete_commentaire', methods: ['POST'])]
    public function deleteCommentaire(Commentaire $commentaire, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($commentaire);
        $em->flush();
    
        return new JsonResponse(['success' => true]);
    }
    #[Route('/commentaire/{id}/edit', name: 'commentaire_edit')]
    public function editCommentaire(Request $request, Commentaire $commentaire)
    {
        // Création du formulaire pour modifier le commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde des modifications
            $this->entityManager->flush(); // Utilisation de l'EntityManager injecté

            // Redirection vers la page du post après la modification
            return $this->redirectToRoute('app_posts', ['id' => $commentaire->getPost()->getIdPost()]);
        }

        return $this->render('/editCommentaire.html.twig', [
            'form' => $form->createView(),
            'commentaire' => $commentaire,
        ]);
    }
    // src/Controller/PostController.php
    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $em): JsonResponse
    {
        $post->setLikes(($post->getLikes() ?? 0) + 1);
        $em->flush();
    
        return $this->json([
            'success' => true,
            'count' => $post->getLikes()
        ]);
    }
    
    #[Route('/post/{id}/dislike', name: 'post_dislike', methods: ['POST'])]
    public function dislike(Post $post, EntityManagerInterface $em): JsonResponse
    {
        $post->setDislikes(($post->getDislikes() ?? 0) + 1);
        $em->flush();
    
        return $this->json([
            'success' => true,
            'count' => $post->getDislikes()
        ]);
    }
}