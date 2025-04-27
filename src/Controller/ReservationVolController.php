<?php

namespace App\Controller;

use App\Entity\Vol;
use App\Repository\ReservationVolRepository;
use App\Entity\ReservationVol;
use App\Form\ReservationVolType;
use App\Repository\VolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\EmailService;



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
                    ->setDateReservation(new \DateTime())
                    ->setIdEtudiant(1);

        $form = $this->createForm(ReservationVolType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            dump("Formulaire soumis et valide !");
            $em->persist($reservation);
            $em->flush();
            return $this->redirectToRoute('show_ticket', ['id' => $reservation->getId()]);
        }
    
        return $this->render('reservation_vol/Ticket_Reser.html.twig', [
            'form' => $form->createView(),
            'vol' => $vol
        ]);
    }

    #[Route('/ticket/show/{id}', name: 'show_ticket')]
    public function showTicket(int $id, ReservationVolRepository $reservationVolRepository): Response
    {
        $reservation = $reservationVolRepository->find($id);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable pour l\'ID : '.$id);
        }

        return $this->render('reservation_vol/showTicket.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/ticket/edit/{id}', name: 'edit_ticket')]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ReservationVolRepository $reservationRepo
    ): Response {
        $reservation = $reservationRepo->find($id);
    
        if (!$reservation) {
            throw $this->createNotFoundException("Réservation introuvable");
        }
    
        $form = $this->createForm(ReservationVolType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('show_ticket', ['id' => $reservation->getId()]);
        }
    
        return $this->render('reservation_vol/editTicket.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }
    #[Route('/ticket/delete/{id}', name: 'delete_ticket')]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        ReservationVolRepository $reservationRepo
    ): Response {
        $reservation = $reservationRepo->find($id);
    
        if (!$reservation) {
            throw $this->createNotFoundException("Réservation introuvable");
        }
    
        $em->remove($reservation);
        $em->flush();
    
        $this->addFlash('success', 'La réservation a été annulée avec succès.');
        return $this->redirectToRoute('app_reservation_vol');
    }




    //pour le  stripe paiement  et mailer 



    #[Route('/payment/checkout/{id}', name: 'payment_checkout')]
public function checkout(int $id, Request $request, ReservationVolRepository $reservationRepo): Response
{
    $reservation = $reservationRepo->find($id);

    if (!$reservation) {
        throw $this->createNotFoundException('Réservation introuvable.');
    }

    Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    // ✅ Stocke l'id de la réservation dans la session Symfony
    $request->getSession()->set('reservation_id', $reservation->getId());

    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur', 
                'product_data' => [
                    'name' => 'Réservation Vol #' . $reservation->getId(),
                ],
                'unit_amount' => $reservation->getPrix() * 100, // ❗ attention: *100 pour convertir en centimes
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
'success_url' => $this->generateUrl('payment_success', [
    'id' => $reservation->getId()
], UrlGeneratorInterface::ABSOLUTE_URL),
      'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return $this->redirect($session->url, 303);
}

#[Route('/payment/cancel', name: 'payment_cancel')]
public function cancel(): Response
{
    $this->addFlash('warning', 'Le paiement a été annulé.');
    return $this->redirectToRoute('app_reservation_vol');
}



private  $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    } 

   // Dans la méthode success
   #[Route('/payment/success/{id}', name: 'payment_success')]
   public function success(int $id, ReservationVolRepository $reservationVolRepository): Response
   {
       $reservation = $reservationVolRepository->find($id);
       
       if (!$reservation) {
           $this->addFlash('danger', 'Réservation introuvable.');
           return $this->redirectToRoute('app_reservation_vol');
       }
   
       try {
           $userEmail = $reservation->getEmail();
           
           $body = $this->renderView('emails/reservation_confirmation.html.twig', [
               'reservation' => $reservation
           ]);
   
           $this->emailService->sendConfirmationEmail($userEmail, 'Confirmation de réservation', $body);
           
           $this->addFlash('success', 'Email de confirmation envoyé !');
       } catch (\Exception $e) {
           $this->addFlash('warning', 'Email non envoyé : ' . $e->getMessage());
       }
   
       return $this->redirectToRoute('app_reservation_vol');
   }







}