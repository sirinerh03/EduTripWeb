<?php

namespace App\Controller;

use App\Entity\Pack_agence;
use App\Form\PackAgenceType;
use App\Repository\Pack_agenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;



final class PackAgenceController extends AbstractController
{
    #[Route('/pack/agence', name: 'app_pack_agence')]
    public function index(Pack_agenceRepository $packAgenceRepository): Response
    {
        $packAgences = $packAgenceRepository->findAll();

        return $this->render('pack_agence/index.html.twig', [
            'packAgences' => $packAgences,
        ]);
    }

    #[Route('/pack/agence/new', name: 'app_pack_agence_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $packAgence = new Pack_agence();
    $form = $this->createForm(PackAgenceType::class, $packAgence);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($packAgence);
        $entityManager->flush();

        $this->addFlash('success', 'Pack d\'agence créé avec succès !');

        return $this->redirectToRoute('app_pack_agence');
    }

    return $this->render('pack_agence/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/pack/agence/{id}', name: 'app_pack_agence_show')]
    public function show(Pack_agence $packAgence): Response
    {
        return $this->render('pack_agence/show.html.twig', [
            'packAgence' => $packAgence,
        ]);
    }

     #[Route('/pack/agence/{id}/edit', name: 'app_pack_agence_edit')]
    public function edit(Request $request, Pack_agence $packAgence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PackAgenceType::class, $packAgence);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Persist changes
            $this->addFlash('success', 'Pack d\'agence mis à jour avec succès !');
            return $this->redirectToRoute('app_pack_agence', ['id' => $packAgence->getIdPack()]);
        }
    
        return $this->render('pack_agence/edit.html.twig', [
            'form' => $form->createView(),
            'packAgence' => $packAgence,
        ]);
    }
    

    #[Route('/pack/agence/{id}/delete', name: 'app_pack_agence_delete')]
    public function delete(Request $request, Pack_agence $packAgence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $packAgence->getIdPack(), $request->request->get('_token'))) {
            $entityManager->remove($packAgence); // Remove entity
            $entityManager->flush(); // Persist changes
    
            $this->addFlash('success', 'Pack d\'agence supprimé avec succès !');
        }
    
        return $this->redirectToRoute('app_pack_agence');
    }
    
}
