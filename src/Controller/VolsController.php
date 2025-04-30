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
use App\Service\AviationStackService;
use Knp\Snappy\Pdf;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ReservationVol;



final class VolsController extends AbstractController
{
    #[Route('/admin/vols', name: 'app_vols')]
    public function index(VolRepository $volRepository, AviationStackService $aviationStackService): Response
    {
       $departure = $request->query->get('departure');
       $arrival = $request->query->get('arrival');
       $date = $request->query->get('date') ? new \DateTime($request->query->get('date')) : null;
    // Filtrage
        $vols = $volRepository->findByFilters($departure, $arrival, $date);

        $vols = $volRepository->findAll(); // correspond à ce que Twig attend
        $apiVols = $aviationStackService->getFlights(); // données API séparées
        return $this->render('admin/vols/vols.html.twig', [
            'vols' => $vols,
            'apiVols' => $apiVols,
            'currentFilters' => [
            'departure' => $departure,
            'arrival' => $arrival,
            'date' => $date ? $date->format('Y-m-d') : null
        ]
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
    public function modifierVol(Request $request, Vol $vol, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VolType::class, $vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vol modifié avec succès !');
            return $this->redirectToRoute('app_vols'); // modifie si besoin
        }

        return $this->render('admin/vols/vol_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/vols/{id}/delete', name: 'vol_delete', methods: ['POST'])]
    public function delete(Request $request, Vol $vol, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vol->getIdVol(), $request->request->get('_token'))) {
            $entityManager->remove($vol);
            $entityManager->flush();

            $this->addFlash('success', '✈️ Le vol a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_vols');
    }





    #[Route('/admin/listreservations', name: 'admin_listreservations')]
public function afficherReservations(\App\Repository\ReservationVolRepository $reservationVolRepository): Response
{
    $reservations = $reservationVolRepository->findAll();

    return $this->render('admin/vols/listreservations.html.twig', [
        'reservations' => $reservations,
    ]);
}


#[Route('/admin/reservations/pdf', name: 'admin_reservations_pdf')]
public function downloadPdf(ManagerRegistry $doctrine, Pdf $knpSnappyPdf): Response
{
    $reservations = $doctrine->getRepository(ReservationVol::class)->findAll();

    $html = $this->renderView('admin/vols/reservations_pdf.html.twig', [
        'reservations' => $reservations,
    ]);

    return new Response(
        $knpSnappyPdf->getOutputFromHtml($html),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="liste_reservations.pdf"',
        ]
    );
}

}
