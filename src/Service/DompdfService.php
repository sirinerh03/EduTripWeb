<?php
namespace App\Service;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapper;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DompdfService
{
    private $twig;
    private $dompdfWrapper;

    public function __construct(Environment $twig, DompdfWrapper $dompdfWrapper)
    {
        $this->twig = $twig;
        $this->dompdfWrapper = $dompdfWrapper;
    }

    /**
     * Génère un PDF à partir d'un template Twig en utilisant Dompdf
     *
     * @param string $template Le template Twig à utiliser
     * @param array $data Les données à passer au template
     * @param string $filename Le nom du fichier PDF
     *
     * @return Response
     */
    public function generatePdf(string $template, array $data, string $filename): Response
    {
        // Générer le HTML à partir du template Twig
        $html = $this->twig->render($template, $data);

        // Utiliser le wrapper du bundle Nucleos pour générer le PDF
        // Les options sont passées directement à la méthode getStreamResponse
        $options = [
            'compress' => true,
            'Attachment' => false,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait'
        ];

        // Générer le PDF
        return $this->dompdfWrapper->getStreamResponse($html, $filename, $options);
    }

    /**
     * Génère un PDF à partir d'un template Twig en utilisant Dompdf et le télécharge
     *
     * @param string $template Le template Twig à utiliser
     * @param array $data Les données à passer au template
     * @param string $filename Le nom du fichier PDF
     *
     * @return Response
     */
    public function downloadPdf(string $template, array $data, string $filename): Response
    {
        // Générer le HTML à partir du template Twig
        $html = $this->twig->render($template, $data);

        // Utiliser le wrapper du bundle Nucleos pour générer le PDF
        // Les options sont passées directement à la méthode getStreamResponse
        $options = [
            'compress' => true,
            'Attachment' => true,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait'
        ];

        // Générer le PDF avec l'option de téléchargement
        return $this->dompdfWrapper->getStreamResponse($html, $filename, $options);
    }
}
