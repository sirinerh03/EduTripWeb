<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route(name: 'app_candidature_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $candidatures = $entityManager->getRepository(Candidature::class)->findAll();

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }

    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $candidature->setEtat('en_attente');

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUploads($form, $candidature);
            $entityManager->persist($candidature);
            $entityManager->flush();

            
        }

        return $this->render('candidature/new.html.twig', [
            'form' => $form->createView(),
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if (!$candidature->getEtat()) {
            $candidature->setEtat('en_attente');
        }

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUploads($form, $candidature);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index');
        }

        return $this->render('candidature/edit.html.twig', [
            'form' => $form->createView(),
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index');
    }

    private function handleFileUploads($form, Candidature $candidature): void
    {
        $uploadFields = [
            'cv' => 'setCv',
            'lettre_motivation' => 'setLettreMotivation',
            'diplome' => 'setDiplome',
        ];

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        foreach ($uploadFields as $field => $setter) {
            $file = $form->get($field)->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalFilename);
                $newFilename = uniqid() . '_' . $safeFilename . '.' . $file->guessExtension();

                try {
                    $file->move($uploadDir, $newFilename);
                    $candidature->$setter($newFilename);
                } catch (FileException $e) {
                    // Optionally log or display error
                }
            }
        }
    }
}
