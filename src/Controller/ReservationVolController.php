<?php

namespace App\Controller;

use App\Entity\Vol;
use App\Entity\ReservationVol;
use App\Form\ReservationVolType;
use App\Repository\VolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationVolController extends AbstractController
{
    #[Route('/reservationvol', name: 'app_reservation_vol')]
    public function index(VolRepository $volRepository): Response
    {
        $vols = $volRepository->findAll();
        return $this->render('reservation_vol/listeREV.html.twig', [
            'vols' => $vols, 
        ]);
    }

    
    #[Route('/reservation_ticket/{id}', name: 'ticket_new')]
public function ajouterReservation($id, Request $request, EntityManagerInterface $entityManager, VolRepository $volRepository): Response
{
    // Récupère le vol en fonction de l'ID
    $vol = $volRepository->find($id);
    if (!$vol) {
        throw $this->createNotFoundException('Vol non trouvé.');
    }

    $reservation = new ReservationVol();
    $form = $this->createForm(ReservationVolType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation enregistrée avec succès !');
        return $this->redirectToRoute('app_reservation_vol');
    }

    return $this->render('reservation_vol/Ticket_Reser.html.twig', [
        'form' => $form->createView(),
        'vol' => $vol, 
    ]);
}
}
