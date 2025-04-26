<?php

namespace App\Controller;
use App\Entity\Utilisateur;  
use App\Entity\Commentaire; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Post;
use App\Entity\Favoris;
use App\Form\PostType;
use App\Form\CommentaireType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\BadWordsChecker;

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
public function posts(Request $request, EntityManagerInterface $entityManager): Response
{
    $postRepository = $entityManager->getRepository(Post::class);
    $search = $request->query->get('search');
    $categorie = $request->query->get('categorie');

    $queryBuilder = $postRepository->createQueryBuilder('p');

    if ($search) {
        $queryBuilder->andWhere('p.contenu LIKE :search')
                     ->setParameter('search', '%' . $search . '%');
    }

    if ($categorie) {
        $queryBuilder->andWhere('p.categorie = :categorie')
                     ->setParameter('categorie', $categorie);
    }

    $posts = $queryBuilder->getQuery()->getResult();

    // S’il s’agit d’une requête AJAX, on retourne une vue partielle
    if ($request->isXmlHttpRequest()) {
        return $this->render('partials/_posts_list.html.twig', [
            'posts' => $posts,
        ]);
    }

    // Sinon, c’est la page normale
    $categories = $entityManager->createQuery('SELECT DISTINCT p.categorie FROM App\Entity\Post p WHERE p.categorie IS NOT NULL')
                                ->getSingleColumnResult();

    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);

    return $this->render('posts.html.twig', [
        'posts' => $posts,
        'form' => $form->createView(),
        'categories' => $categories,
    ]);
}


    #[Route('/commentaire/add', name: 'ajouter_commentaire', methods: ['POST'])]
    public function ajouterCommentaire(
        Request $request,
        EntityManagerInterface $em,
        HttpClientInterface $client,
        PostRepository $postRepository
    ): JsonResponse {
        try {
            // 1. Récupération et validation des données
            $postId = $request->request->get('postId');
            $contenu = $request->request->get('contenu');
        
            if (empty($postId)) {
                return new JsonResponse(['error' => 'L\'identifiant du post est requis'], 400);
            }
            
            if (empty($contenu)) {
                return new JsonResponse(['error' => 'Le contenu du commentaire ne peut pas être vide'], 400);
            }
    
            // 2. Vérification de l'existence du post
            $post = $postRepository->find($postId);
            if (!$post) {
                return new JsonResponse(['error' => 'Le post spécifié n\'existe pas'], 404);
            }
    
            // 3. Vérification des mots interdits avec gestion d'erreur améliorée
            try {
                $response = $client->request('POST', 'https://api.api-ninjas.com/v1/profanityfilter', [
                    'headers' => [
                        'X-Api-Key' => $this->getParameter('badwords_api_key'),
                        'Content-Type' => 'application/json',
                    ],
                    'json' => ['text' => $contenu],
                    'timeout' => 3 // Timeout de 3 secondes
                ]);
    
                $result = $response->toArray();
    
                if (($result['has_bad_words'] ?? false) === true) {
                    return new JsonResponse([
                        'error' => 'Votre commentaire contient des termes inappropriés',
                        'filtered_text' => $result['filtered_text'] ?? null
                    ], 422);
                }
            } catch (\Exception $apiError) {
                // Fallback si l'API échoue
                $badWords = ['stupid', 'fuck','shit']; // Liste basique de mots interdits
                foreach ($badWords as $word) {
                    if (stripos($contenu, $word) !== false) {
                        return new JsonResponse([
                            'error' => 'Votre commentaire contient des termes inappropriés',
                            'filtered_text' => str_ireplace($badWords, '***', $contenu)
                        ], 422);
                    }
                }
            }
    
            // 4. Création et persistance du commentaire
            $commentaire = (new Commentaire())
                ->setContenu($contenu)
                ->setPost($post)
                ->setDateCommentaire(new \DateTime())
                ->setUtilisateur($em->getRepository(Utilisateur::class)->find(1) ?? 
                    throw new \RuntimeException('Utilisateur par défaut introuvable'));
    
            $em->persist($commentaire);
            $em->flush();
    
            // 5. Retour de la réponse
            return new JsonResponse([
                'success' => true,
                'commentaire' => [
                    
                    'contenu' => $commentaire->getContenu(),
                    'username' => $commentaire->getUtilisateur()->getPrenom(),
                    'date' => $commentaire->getDateCommentaire()->format('d/m/Y H:i')
                ]
            ]);
    
        } catch (\Exception $e) {
            // 6. Gestion des erreurs inattendues
            return new JsonResponse([
                'error' => 'Une erreur technique est survenue',
                'details' => $e->getMessage() // À désactiver en production
            ], 500);
        }
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
    #[Route('/post/{id}/favorite', name: 'post_favorite', methods: ['POST'])]
    public function toggleFavorite(Post $post, EntityManagerInterface $em): JsonResponse
    {
        $etudiant = $em->getRepository(Utilisateur::class)->find(1);
        
        $favoris = $em->getRepository(Favoris::class)
            ->findOneBy([
                'post' => $post,
                'utilisateur' => $etudiant
            ]);
    
        if ($favoris) {
            $em->remove($favoris);
            $em->flush();
            
            return $this->json([
                'success' => true,
                'isFavorite' => false,
                'message' => 'Retiré des favoris'
            ]);
        }
        
        return $this->json([
            'success' => false,
            'message' => 'Non trouvé dans les favoris'
        ], 404);
    }
    
    #[Route('/mes-favoris', name: 'app_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        // Récupérer l'étudiant avec ID 1
        $etudiant = $em->getRepository(Utilisateur::class)->find(1);
        
        if (!$etudiant) {
            $this->addFlash('error', 'Étudiant introuvable');
            return $this->redirectToRoute('app_posts');
        }
    
        // Récupérer les favoris avec jointure
        $favoris = $em->getRepository(Favoris::class)
            ->createQueryBuilder('f')
            ->join('f.post', 'p')
            ->where('f.utilisateur = :etudiant')
            ->setParameter('etudiant', $etudiant)
            ->orderBy('f.id_favoris', 'DESC')
            ->getQuery()
            ->getResult();
    
        // Extraire les posts
        $posts = array_map(function($f) { return $f->getPost(); }, $favoris);
    
        return $this->render('post/favorites.html.twig', [
            'posts' => $posts,
            'etudiant_id' => 1 // Pour référence dans le template
        ]);
    }
}