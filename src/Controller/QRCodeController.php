<?php

namespace App\Controller;

use App\Entity\University;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class QRCodeController extends AbstractController
{
    #[Route('/university-qr/university/{id}', name: 'app_qr_code_university', methods: ['GET'])]
    public function universityQr(int $id, EntityManagerInterface $entityManager): Response
    {
        $university = $entityManager->getRepository(University::class)->find($id);
        if (!$university) {
            throw $this->createNotFoundException('University not found');
        }
        $url = 'mailto:' . $university->getEmail() . '?subject=Demande d\'information&body=Bonjour, je souhaite en savoir plus sur votre université.';
        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            size: 200,
            margin: 10
        );
        $qr = $builder->build();
        return new Response($qr->getString(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    
    #[Route('/university-qr/custom-download/{id}', name: 'app_qr_code_download', methods: ['GET'])]
    public function downloadQr(int $id, EntityManagerInterface $entityManager): Response
    {
        $university = $entityManager->getRepository(University::class)->find($id);
        if (!$university) {
            throw $this->createNotFoundException('University not found');
        }
        $url = 'mailto:' . $university->getEmail() . '?subject=Demande d\'information&body=Bonjour, je souhaite en savoir plus sur votre université.';
        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            size: 400,
            margin: 10
        );
        $qr = $builder->build();
        return new Response($qr->getString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="university-qr-'.$id.'.png"',
        ]);
    }
}
