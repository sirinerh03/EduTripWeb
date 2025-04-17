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
    public function avisList(AvisRepository $avisRepository): Response
    {
        $allAvis = $avisRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/avis/list.html.twig', [
            'avis' => $allAvis,
        ]);
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
    public function usersList(UserRepository $userRepository): Response
    {
        $allUsers = $userRepository->findBy([], ['id' => 'DESC']);
        
        return $this->render('admin/user/list.html.twig', [
            'users' => $allUsers,
        ]);
    }
} 