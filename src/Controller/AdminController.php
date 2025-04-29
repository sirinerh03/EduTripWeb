<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\CommentaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Utilisateur;  

use App\Entity\Commentaire; 
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{
    
    #[Route('/admin_posts', name: 'admin_posts')]
    public function index(Request $request, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('q');
        $selectedCategorie = $request->query->get('categorie');
    
        // Recherche seule
        if ($searchTerm) {
            $query = $postRepository->findWithSearch($searchTerm);
        }
        // Filtrage seul
        elseif ($selectedCategorie && $selectedCategorie !== 'all') {
            $query = $postRepository->findWithFilter($selectedCategorie);
        }
        // Aucun filtre
        else {
            $query = $postRepository->findAllPosts();
        }
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
    
        $categories = $postRepository->findAllCategories(); // méthode pour les <select>
    
        return $this->render('admin/adminposts.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'selectedCategorie' => $selectedCategorie,
            'categories' => $categories,
        ]);
    }
    
    
    
   

    #[Route('/admin/post/{id}', name: 'app_post_detail')]
public function detail(Post $post): Response
{
    return $this->render('admin/detailsPosts.html.twig', [
        'post' => $post,
        'commentaires' => $post->getCommentaires(),
    ]);
}
#[Route('/admin/post/{id}/delete', name: 'admin_post_delete')]
public function delete(PostRepository $postRepository, EntityManagerInterface $em, int $id): RedirectResponse
{
    $post = $postRepository->find($id);

    if (!$post) {
        throw $this->createNotFoundException('Post non trouvé.');
    }

    $em->remove($post);
    $em->flush();

    $this->addFlash('success', 'Le post a été supprimé avec succès.');

    return $this->redirectToRoute('admin_posts');
}
#[Route('/admin/commentaire/{id}/delete', name: 'admin_comment_delete')]
public function deleteCommentaire(int $id, CommentaireRepository $commentaireRepository, EntityManagerInterface $em): RedirectResponse
{
    $commentaire = $commentaireRepository->find($id);

    if (!$commentaire) {
        throw $this->createNotFoundException('Commentaire non trouvé.');
    }

    $idPost = $commentaire->getPost()->getIdPost(); // pour rediriger vers la page du post

    $em->remove($commentaire);
    $em->flush();

    $this->addFlash('success', 'Commentaire supprimé avec succès.');

    return $this->redirectToRoute('app_post_detail', ['id' => $idPost]);
}


#[Route('/recherche', name: 'app_recherche')]
public function recherche(Request $request, PostRepository $postRepo, UtilisateurRepository $utilRepo): Response
{
    $query = $request->query->get('q');

    $posts = $postRepo->findByTitleOrContent($query);
    $users = $utilRepo->findByNomOrEmail($query);
    // Ajoute ici d'autres entités à rechercher

    return $this->render('search/index.html.twig', [
        'query' => $query,
        'posts' => $posts,
        'users' => $users,
        // Autres résultats
    ]);
}



}