<?php


// AgenceController.php

namespace App\Controller;

use App\Entity\Agence;
use App\Form\AgenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agence')]
final class AgenceController extends AbstractController
{
    #[Route('/', name: 'app_agence_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $agences = $entityManager
            ->getRepository(Agence::class)
            ->findAll();

        return $this->render('agence/index.html.twig', [
            
            'agences' => $agences,
        ]);
    }
   


    #[Route('/Liste', name: 'app_agence_liste', methods: ['GET'])]
    public function simpleList(Request $request, EntityManagerInterface $entityManager): Response
{
    $nom = $request->query->get('nom');
    $description = $request->query->get('description');
    $dateCreation = $request->query->get('date_creation');
    
    $queryBuilder = $entityManager->getRepository(Agence::class)->createQueryBuilder('a');
    
    if ($nom) {
        $queryBuilder->andWhere('a.nom_ag LIKE :nom')
                    ->setParameter('nom', '%'.$nom.'%');
    }
    
    if ($description) {
        $queryBuilder->andWhere('a.description_ag LIKE :description')
                    ->setParameter('description', '%'.$description.'%');
    }
    
    if ($dateCreation) {
        $date = \DateTime::createFromFormat('Y-m-d', $dateCreation);
        if ($date) {
            $queryBuilder->andWhere('a.date_creation = :date')
                        ->setParameter('date', $date);
        }
    }
    
    $agences = $queryBuilder->getQuery()->getResult();
    
    // Si c'est une requête AJAX, retourne uniquement le HTML du tableau
    if ($request->isXmlHttpRequest()) {
        return $this->render('agence/_agences_cards.html.twig', [
            'agences' => $agences
        ]);
    }
    
    // Sinon, retourne la page complète
    return $this->render('agence/index2.html.twig', [
        'agences' => $agences,
    ]);
}
    

    #[Route('/new', name: 'app_agence_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $agence = new Agence();
    $form = $this->createForm(AgenceType::class, $agence);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $entityManager->persist($agence);
            $entityManager->flush();

            $this->addFlash('success', 'L\'agence a été créée avec succès.');
            return $this->redirectToRoute('app_agence_index');
        } else {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
        }
    }

    return $this->render('agence/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    
#[Route('/{id_agence}', name: 'app_agence_show', methods: ['GET'])]
    public function show(Agence $agence): Response
    {
        return $this->render('agence/show.html.twig', [
            'agence' => $agence,
        ]);
    }

    #[Route('/{id_agence}/edit', name: 'app_agence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Agence $agence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AgenceType::class, $agence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Agence modifiée avec succès.');

            return $this->redirectToRoute('app_agence_index');  // Redirection vers l'index
        }

        return $this->render('agence/edit.html.twig', [
            'agence' => $agence,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_agence}', name: 'app_agence_delete', methods: ['POST'])]
    public function delete(Request $request, Agence $agence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $agence->getIdAgence(), $request->request->get('_token'))) {
            $entityManager->remove($agence);
            $entityManager->flush();

            $this->addFlash('success', 'Agence supprimée avec succès.');
        }

        return $this->redirectToRoute('app_agence_index');  // Redirection vers l'index
    }
}
