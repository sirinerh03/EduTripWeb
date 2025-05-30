<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use App\Service\SpinRewardService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/avis')]
class AvisController extends AbstractController
{
    private $spinRewardService;

    public function __construct(SpinRewardService $spinRewardService)
    {
        $this->spinRewardService = $spinRewardService;
    }
    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findLatestAvis(),
        ]);
    }

    #[Route('/new', name: 'app_avis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        $avi = new Avis();
        // Ne pas définir de note par défaut pour forcer l'utilisateur à en choisir une

        $form = $this->createForm(AvisType::class, $avi);

        if ($request->isMethod('POST')) {
            try {
                // Get the form data directly from the request
                $submittedData = $request->request->all();

                // Vérifier d'abord si le champ de débogage est présent
                if (isset($submittedData['debug_rating']) && !empty($submittedData['debug_rating'])) {
                    $rating = (int)$submittedData['debug_rating'];
                    $avi->setRating($rating);
                    // Log pour débogage
                    error_log("Utilisation de la valeur de debug_rating: " . $rating);
                }
                // Sinon, extraire la note du formulaire standard
                elseif (isset($submittedData['avis']) && is_array($submittedData['avis']) && isset($submittedData['avis']['rating'])) {
                    $rating = (int)$submittedData['avis']['rating'];
                    $avi->setRating($rating);
                    // Log pour débogage
                    error_log("Utilisation de la valeur standard: " . $rating);
                }

                // Vérifier également le champ personnalisé
                if (isset($submittedData['custom_rating'])) {
                    $customRating = (int)$submittedData['custom_rating'];
                    $avi->setRating($customRating);
                    // Log pour débogage
                    error_log("Utilisation de la valeur custom_rating: " . $customRating);
                }

                // Set the comment if it exists
                if (isset($submittedData['avis']) && is_array($submittedData['avis']) && isset($submittedData['avis']['comment'])) {
                    $comment = $submittedData['avis']['comment'];
                    $avi->setComment($comment);
                }

                // Set additional required data
                $avi->setUser($this->getUser());
                $avi->setCreatedAt(new \DateTimeImmutable());

                // Validate manually
                $errors = $this->validateAvis($avi);

                if (empty($errors)) {
                    $entityManager->persist($avi);
                    $entityManager->flush();

                    // Attribuer une récompense aléatoire
                    $reward = $this->spinRewardService->assignRandomReward($avi);

                    if ($reward) {
                        $this->addFlash('success', 'Votre avis a été ajouté avec succès ! Vous avez gagné une récompense !');
                        return $this->redirectToRoute('app_spin_reward', ['id' => $avi->getId()]);
                    } else {
                        $this->addFlash('success', 'Votre avis a été ajouté avec succès !');
                        return $this->redirectToRoute('app_avis_index');
                    }
                } else {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error);
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du traitement du formulaire: ' . $e->getMessage());
            }
        }

        return $this->render('avis/new.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    /**
     * Simple validation for Avis entity
     */
    private function validateAvis(Avis $avis): array
    {
        $errors = [];

        // Validate rating
        if (null === $avis->getRating()) {
            $errors[] = 'Veuillez sélectionner une note.';
        } elseif ($avis->getRating() < 1 || $avis->getRating() > 5) {
            $errors[] = 'La note doit être comprise entre 1 et 5.';
        }

        // Validate comment
        if (null === $avis->getComment() || trim($avis->getComment()) === '') {
            $errors[] = 'Le commentaire est obligatoire.';
        } elseif (strlen($avis->getComment()) < 10) {
            $errors[] = 'Le commentaire doit faire au moins 10 caractères.';
        } elseif (strlen($avis->getComment()) > 1000) {
            $errors[] = 'Le commentaire ne peut pas dépasser 1000 caractères.';
        }

        return $errors;
    }

    #[Route('/{id}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'avis appartient à l'utilisateur connecté
        $isOwner = ($avi->getUser() === $this->getUser());

        return $this->render('avis/show.html.twig', [
            'avi' => $avi,
            'isOwner' => $isOwner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        if ($avi->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cet avis.');
            return $this->redirectToRoute('app_avis_index');
        }

        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été modifié avec succès !');
            return $this->redirectToRoute('app_avis_index');
        }

        return $this->render('avis/edit.html.twig', [
            'avi' => $avi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        if ($avi->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet avis.');
            return $this->redirectToRoute('app_avis_index');
        }

        if ($this->isCsrfTokenValid('delete'.$avi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avi);
            $entityManager->flush();
            $this->addFlash('success', 'Votre avis a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_avis_index');
    }
}
