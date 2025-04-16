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

    #[Route('/ticket/new/{idVol}', name: 'ticket_new')]
    public function new(
        Request $request, 
        int $idVol, 
        EntityManagerInterface $em, 
        VolRepository $volRepository
    ): Response {
        $vol = $volRepository->find($idVol);
        
        if (!$vol) {
            throw $this->createNotFoundException('Vol introuvable pour l\'ID : '.$idVol);
        }
    
        $reservation = new ReservationVol();
        $reservation->setVol($vol)
                    ->setPrix($vol->getPrixVol())
                    ->setDateReservation(new \DateTime());
    
        $form = $this->createForm(ReservationVolType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reservation);
            $em->flush();
    
            $this->addFlash('success', 'Réservation confirmée !');
            return $this->redirectToRoute('app_reservation_vol');
        }
    
        return $this->render('reservation_vol/Ticket_Reser.html.twig', [
            'form' => $form->createView(),
            'vol' => $vol
        ]);
    }
}
