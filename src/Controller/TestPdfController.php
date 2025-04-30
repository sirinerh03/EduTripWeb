<?php

namespace App\Controller;

use App\Service\DompdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestPdfController extends AbstractController
{
    #[Route('/test/pdf', name: 'app_test_pdf')]
    public function testPdf(DompdfService $dompdfService): Response
    {
        // Générer un PDF de test
        return $dompdfService->generatePdf(
            'test/pdf_test.html.twig',
            [
                'title' => 'Test PDF avec le bundle Nucleos DomPDF',
                'content' => 'Ceci est un test de génération de PDF avec le bundle Nucleos DomPDF',
                'date' => new \DateTime()
            ],
            'test_pdf.pdf'
        );
    }
}
