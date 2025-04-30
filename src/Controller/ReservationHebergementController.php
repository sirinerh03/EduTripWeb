<?php

namespace App\Controller;

use App\Repository\HebergementRepository;
use App\Entity\Hebergement;
use App\Entity\ReservationHebergement;
use App\Form\ReservationHebergementType;
use App\Repository\ReservationHebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation/hebergement')]
final class ReservationHebergementController extends AbstractController
{
    #[Route(name: 'app_reservation_hebergement_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, ReservationHebergementRepository $reservationHebergementRepository): Response
    {
        $query = $reservationHebergementRepository->createQueryBuilder('r')->getQuery();
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
    
        return $this->render('reservation_hebergement/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

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

        if ($hebergement->getCapaciteh() <= 0) {
            $this->addFlash('error', 'La capacité de cet hébergement est déjà pleine.');
            return $this->redirectToRoute('app_hebergement_index');
        }

        $reservation = new ReservationHebergement();
        $reservation->setHebergement($hebergement);

        $form = $this->createForm(ReservationHebergementType::class, $reservation, [
            'hebergement_name' => $hebergement->getNomh()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hebergement->setCapaciteh($hebergement->getCapaciteh() - 1);

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation ajoutée avec succès et capacité mise à jour.');
            return $this->redirectToRoute('app_reservation_hebergement_index');
        }

        return $this->render('reservation_hebergement/new.html.twig', [
            'form' => $form->createView(),
            'hebergement' => $hebergement
        ]);
    }

    #[Route('/new-carousel/{id_hebergement}', name: 'app_reservation_hebergement_new_carousel')]
    public function newCarousel(
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
            'hebergement_name' => $hebergement->getNomh(),
            'is_new' => true
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $hebergement->setCapaciteh($hebergement->getCapaciteh() - 1);
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            $this->addFlash('success', 'Réservation ajoutée avec succès et capacité mise à jour.');
            return $this->redirectToRoute('app_reservation_hebergement_show_carousel', [
                'id_reservationh' => $reservation->getIdReservationh()
            ]);
        }
    
        return $this->render('reservation_hebergement/new_carousel.html.twig', [
            'form' => $form->createView(),
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
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement, [
            'is_new' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_reservation_hebergement_index');
        }

        return $this->render('reservation_hebergement/edit.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservation/{id_reservationh}/delete', name: 'app_reservation_hebergement_delete', methods: ['POST'])]
    public function deleteReservation(ReservationHebergement $reservationHebergement, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservationHebergement->getIdReservationh(), $request->get('_token'))) {
            $entityManager->remove($reservationHebergement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_hebergement_index');
    }

    #[Route('/reservation/show/{id_reservationh}', name: 'app_reservation_hebergement_show_carousel', methods: ['GET'])]
    public function showReservationCarousel(ReservationHebergement $reservationHebergement): Response
    {
        return $this->render('reservation_hebergement/show_carousel.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
        ]);
    }

    #[Route('/reservation/carousel/{id_reservationh}/delete', name: 'app_reservation_hebergement_delete_carousel', methods: ['POST'])]
    public function deleteCarousel(ReservationHebergement $reservationHebergement, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservationHebergement->getIdReservationh(), $request->get('_token'))) {
            $entityManager->remove($reservationHebergement);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès.');
        }

        return $this->redirectToRoute('app_hebergement_carousel');
    }

    #[Route('/reservation/carousel/{id_reservationh}/edit', name: 'app_reservation_hebergement_edit_carousel', methods: ['GET', 'POST'])]
    public function editCarousel(Request $request, ReservationHebergement $reservationHebergement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Réservation mise à jour avec succès.');
            return $this->redirectToRoute('app_reservation_hebergement_show_carousel', [
                'id_reservationh' => $reservationHebergement->getIdReservationh()
            ]);
        }

        return $this->render('reservation_hebergement/edit_carousel.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
            'form' => $form->createView(),
        ]);
    }
    
    
}