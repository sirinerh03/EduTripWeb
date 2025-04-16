<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route(name: 'app_candidature_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $candidatures = $entityManager
            ->getRepository(Candidature::class)
            ->findAll();

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }


    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $candidature->setEtat('en_attente'); // Ensure 'etat' is set during creation
    
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFileUploads($form, $candidature); // Handle file uploads for CV, Lettre, Diplome
    
            $entityManager->persist($candidature);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_candidature_index');
        }
    
        return $this->render('candidature/new.html.twig', [
            'form' => $form->createView(),
            'candidature' => $candidature,  // Pass the 'candidature' variable to the template
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
        // Ensure 'etat' is not null during editing
        if (!$candidature->getEtat()) {
            $candidature->setEtat('en_attente'); // Default value if 'etat' is not set
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
        $uploadedFiles = [
            'cv' => 'setCv',
            'lettre_motivation' => 'setLettreMotivation',
            'diplome' => 'setDiplome',
        ];

        foreach ($uploadedFiles as $field => $method) {
            $file = $form->get($field)->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploads', $fileName);//Uploads each file to the public/uploads/ folder.


                $candidature->$method($fileName);
            }
        }
    }
}


