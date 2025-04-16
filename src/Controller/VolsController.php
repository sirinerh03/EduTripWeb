<?php

namespace App\Controller;

use App\Entity\Vol;
use App\Form\VolType;
use App\Repository\VolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VolsController extends AbstractController
{
    #[Route('/vols', name: 'app_vols')]
    public function liste(VolRepository $volRepository): Response
    {
        $vols = $volRepository->findAll();

        return $this->render('admin/vols/vols.html.twig', [
            'vols' => $vols,
        ]);
    }

    #[Route('/vols/new', name: 'vol_new')]
    public function ajouterVol(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vol = new Vol();
        $form = $this->createForm(VolType::class, $vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vol);
            $entityManager->flush();

            return $this->redirectToRoute('app_vols');
        } else {
            dump($form);
        }
            
        return $this->render('admin/vols/vol_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/vols/edit/{id}', name: 'vol_edit')]
    public function edit(Vol $vol, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VolType::class, $vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le vol a été modifié avec succès.');
            return $this->redirectToRoute('app_vols');
        }

        return $this->render('admin/vols/vol_edit.html.twig', [
            'form' => $form->createView(),
            'vol' => $vol,
        ]);
    }

    #[Route('/vols/{id}/delete', name: 'vol_delete', methods: ['POST'])]
    public function delete(Request $request, Vol $vol, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vol->getId_vol(), $request->request->get('_token'))) {
            $entityManager->remove($vol);
            $entityManager->flush();

            $this->addFlash('success', '✈️ Le vol a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_vols');
    }
}
