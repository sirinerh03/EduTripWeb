<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Avis;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository, AvisRepository $avisRepository): Response
    {
        // Get total counts
        $totalUsers = $userRepository->count([]);
        $totalReviews = $avisRepository->count([]);

        // Get recent users and reviews (limit to 5)
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $recentReviews = $avisRepository->findLatestAvis(5);

        return $this->render('admin/homeadmin.html.twig', [
            'totalUsers' => $totalUsers,
            'totalReviews' => $totalReviews,
            'recentUsers' => $recentUsers,
            'recentReviews' => $recentReviews,
        ]);
    }

    #[Route('/dashboard', name: 'base_admin')]
    public function dashboard(): Response
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/avis', name: 'admin_avis_list')]
    public function avisList(Request $request, AvisRepository $avisRepository, UserRepository $userRepository): Response
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

            // Construire les critères de recherche
            $criteria = [
                'sort_field' => $sortField,
                'sort_order' => $sortOrder
            ];

            if (!empty($search)) {
                $criteria['search'] = $search;
            }

            if (!empty($rating)) {
                $criteria['rating'] = $rating;
            }

            if (!empty($userId)) {
                $criteria['user_id'] = $userId;
            }

            if (!empty($dateFrom)) {
                $criteria['date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $criteria['date_to'] = $dateTo;
            }

            // Récupérer les avis filtrés
            $allAvis = $avisRepository->searchAvis($criteria);

            // Récupérer tous les utilisateurs pour le filtre
            $users = $userRepository->findAll();

            // Vérifier que $allAvis est bien un tableau
            if (!is_array($allAvis)) {
                $allAvis = [];
            }

            return $this->render('admin/avis/list.html.twig', [
                'avis' => $allAvis,
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

            return $this->render('admin/avis/list.html.twig', [
                'avis' => [],
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

    #[Route('/avis/{id}/delete', name: 'admin_avis_delete')]
    public function deleteAvis(Request $request, Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_avis_list');
    }

    #[Route('/user/{id}/delete', name: 'admin_user_delete')]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Don't allow deleting your own account
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/user/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Store original role if user is admin
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Create the form
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if a new password was provided
            if ($plainPassword = $form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Ensure admin users keep their admin role
            if ($isAdmin) {
                $user->setRoles(['ROLE_ADMIN']);
            }

            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été modifié avec succès.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users', name: 'admin_users_list')]
    public function usersList(Request $request, UserRepository $userRepository): Response
    {
        // Récupérer les paramètres de recherche et filtrage
        $search = $request->query->get('search');
        $role = $request->query->get('role');
        $status = $request->query->get('status');
        $action = $request->query->get('action');
        $sortField = $request->query->get('sort_field', 'id');
        $sortOrder = $request->query->get('sort_order', 'DESC');

        // Construire les critères de recherche
        $criteria = [
            'sort_field' => $sortField,
            'sort_order' => $sortOrder
        ];

        // Si l'action est "search", on ne prend en compte que le champ de recherche
        if ($action === 'search' && $search) {
            $criteria['search'] = $search;
        }
        // Si l'action est "filter", on ne prend en compte que les filtres
        elseif ($action === 'filter') {
            if ($role) {
                $criteria['role'] = $role;
            }
            if ($status) {
                $criteria['status'] = $status;
            }
        }
        // Si aucune action spécifiée, on prend tout en compte (comportement par défaut)
        else {
            if ($search) {
                $criteria['search'] = $search;
            }
            if ($role) {
                $criteria['role'] = $role;
            }
            if ($status) {
                $criteria['status'] = $status;
            }
        }

        // Récupérer les utilisateurs filtrés
        $allUsers = $userRepository->searchUsers($criteria);

        $countUser = 0;
    $countAgency = 0;
    $countAdmin = 0;

    foreach ($allUsers as $user) {
        foreach ($user->getRoles() as $r) {
            if ($r === 'ROLE_USER') {
                $countUser++;
            } elseif ($r === 'ROLE_AGENCY') {
                $countAgency++;
            } elseif ($r === 'ROLE_ADMIN') {
                $countAdmin++;
            }
        }
    }

        return $this->render('admin/user/list.html.twig', [
            'users' => $allUsers,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'sort_field' => $sortField,
            'sort_order' => $sortOrder,
            'countUser' => $countUser,
        'countAgency' => $countAgency,
        'countAdmin' => $countAdmin,
        ]);
    }
}