<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/avis')]
#[IsGranted('ROLE_ADMIN')]
class AdminAvisController extends AbstractController
{
    #[Route('/', name: 'admin_avis_index')]
    public function index(Request $request, AvisRepository $avisRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            // Récupérer les paramètres de recherche et filtrage avec des valeurs par défaut
            $search = $request->query->get('search', '');
            $rating = $request->query->get('rating', '');
            $userId = $request->query->get('user_id', '');
            $dateFrom = $request->query->get('date_from', '');
            $dateTo = $request->query->get('date_to', '');
            $sortField = $request->query->get('sort_field', 'createdAt');
            $sortOrder = $request->query->get('sort_order', 'DESC');

            // Créer une requête personnalisée pour la recherche et le filtrage
            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('a')
                ->from('App\Entity\Avis', 'a')
                ->leftJoin('a.user', 'u');

            // Appliquer les filtres
            if (!empty($search)) {
                $queryBuilder->andWhere('a.comment LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }

            if (!empty($rating)) {
                $queryBuilder->andWhere('a.rating = :rating')
                    ->setParameter('rating', $rating);
            }

            if (!empty($userId)) {
                $queryBuilder->andWhere('a.user = :userId')
                    ->setParameter('userId', $userId);
            }

            // Suppression des filtres de date

            // Appliquer le tri
            if ($sortField === 'createdAt') {
                $queryBuilder->orderBy('a.createdAt', $sortOrder);
            } elseif ($sortField === 'rating') {
                $queryBuilder->orderBy('a.rating', $sortOrder);
            } elseif ($sortField === 'user') {
                $queryBuilder->orderBy('u.nom', $sortOrder)
                    ->addOrderBy('u.prenom', $sortOrder);
            }

            // Exécuter la requête
            $allAvis = $queryBuilder->getQuery()->getResult();

            // Récupérer tous les utilisateurs pour le filtre
            $users = $userRepository->findAll();

            return $this->render('admin/avis/index.html.twig', [
                'all_avis' => $allAvis,
                'users' => $users,
                'search' => $search,
                'rating' => $rating,
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort_field' => $sortField,
                'sort_order' => $sortOrder
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur, afficher un message et retourner une liste vide
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération des avis: ' . $e->getMessage());

            return $this->render('admin/avis/index.html.twig', [
                'all_avis' => [],
                'users' => $userRepository->findAll(),
                'search' => '',
                'rating' => '',
                'user_id' => '',
                'date_from' => '',
                'date_to' => '',
                'sort_field' => 'createdAt',
                'sort_order' => 'DESC'
            ]);
        }
    }

    #[Route('/{id}/delete', name: 'admin_avis_delete_new')]
    public function delete(Request $request, Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_avis_index');
    }
}
