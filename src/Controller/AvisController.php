<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
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
    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_avis_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photo')->getData();

            if ($file) {
                $filename = uniqid('', true) . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('avis_photos_directory'),
                        $filename
                    );
                    $avis->setPhoto($filename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            // Set the current user as the author
            $avis->setUser($this->getUser());

            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Votre avis a été ajouté avec succès.');
            return $this->redirectToRoute('app_avis_index');
        }

        return $this->render('avis/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        return $this->render('avis/show.html.twig', [
            'avi' => $avi,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi, EntityManagerInterface $em): Response
    {
        // Check if the user is the author of the avis or an admin
        if ($avi->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cet avis.');
            return $this->redirectToRoute('app_avis_index');
        }

        $form = $this->createForm(AvisType::class, $avi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photo')->getData();

            if ($file) {
                $filename = uniqid('', true) . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('avis_photos_directory'),
                        $filename
                    );
                    $avi->setPhoto($filename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Votre avis a été modifié avec succès.');
            return $this->redirectToRoute('app_avis_index');
        }

        return $this->render('avis/edit.html.twig', [
            'avi' => $avi,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi, EntityManagerInterface $em): Response
    {
        // Check if the user is the author of the avis or an admin
        if ($avi->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet avis.');
            return $this->redirectToRoute('app_avis_index');
        }

        if ($this->isCsrfTokenValid('delete' . $avi->getId(), $request->request->get('_token'))) {
            $em->remove($avi);
            $em->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_avis_index');
    }
}
