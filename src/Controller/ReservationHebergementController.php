<?php

namespace App\Controller;
use App\Repository\HebergementRepository; 

use App\Entity\Hebergement;
use App\Entity\ReservationHebergement;
use App\Form\ReservationHebergementType;
use App\Repository\ReservationHebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation/hebergement')]
final class ReservationHebergementController extends AbstractController
{
    #[Route(name: 'app_reservation_hebergement_index', methods: ['GET'])]
    public function index(ReservationHebergementRepository $reservationHebergementRepository): Response
    {
        return $this->render('reservation_hebergement/index.html.twig', [
            'reservation_hebergements' => $reservationHebergementRepository->findAll(),
        ]);
    }
// src/Controller/ReservationHebergementController.php

#[Route('/new/{id_hebergement}', name: 'app_reservation_hebergement_new')]
public function new(
    Request $request, 
    HebergementRepository $hebergementRepository, 
    EntityManagerInterface $entityManager, 
    int $id_hebergement
): Response {
    $hebergement = $hebergementRepository->find($id_hebergement);

    if (!$hebergement) {
        throw $this->createNotFoundException('Hébergement non trouvé.');
    }

    $reservation = new ReservationHebergement();
    $reservation->setHebergement($hebergement);

    $form = $this->createForm(ReservationHebergementType::class, $reservation, [
        'hebergement_name' => $hebergement->getNomh() // Pass the hebergement name here
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->redirectToRoute('app_reservation_hebergement_index');
    }

    return $this->render('reservation_hebergement/new.html.twig', [
        'form' => $form,
        'hebergement' => $hebergement
    ]);
}

    #[Route('/{id_reservationh}', name: 'app_reservation_hebergement_show', methods: ['GET'])]
    public function show(ReservationHebergement $reservationHebergement): Response
    {
        return $this->render('reservation_hebergement/show.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
        ]);
    }

    #[Route('/{id_reservationh}/edit', name: 'app_reservation_hebergement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationHebergement $reservationHebergement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_hebergement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation_hebergement/edit.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
            'form' => $form,
        ]);
    }

    #[Route('/reservation/{id_reservationh}/delete', name: 'app_reservation_hebergement_delete', methods: ['POST'])]
    public function deleteReservation(ReservationHebergement $reservationHebergement, Request $request, EntityManagerInterface $entityManager): Response
    {
        // CSRF Token validation
        if ($this->isCsrfTokenValid('delete' . $reservationHebergement->getIdReservationh(), $request->get('_token'))) {
            // Remove the reservation
            $entityManager->remove($reservationHebergement);
            $entityManager->flush();
        }
    
        // Redirect to the reservation index page
        return $this->redirectToRoute('app_reservation_hebergement_index');
    }

}
