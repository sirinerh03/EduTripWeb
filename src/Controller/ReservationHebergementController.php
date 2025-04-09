<?php

namespace App\Controller;

use App\Entity\ReservationHebergement;
use App\Form\ReservationHebergementType;
use App\Repository\ReservationHebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/new', name: 'app_reservation_hebergement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationHebergement = new ReservationHebergement();
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservationHebergement);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_hebergement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation_hebergement/new.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
            'form' => $form,
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

    #[Route('/{id_reservationh}', name: 'app_reservation_hebergement_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationHebergement $reservationHebergement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationHebergement->getIdReservationh(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationHebergement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_hebergement_index', [], Response::HTTP_SEE_OTHER);
    }
}
