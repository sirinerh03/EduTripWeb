<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfService
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Génère un PDF à partir d'un template Twig
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

        // Créer une réponse HTTP avec le contenu HTML
        $response = new Response();

        // Nous utilisons text/html au lieu de application/pdf pour éviter les problèmes de compatibilité
        $response->headers->set('Content-Type', 'text/html');

        // Ajouter des styles spécifiques pour l'impression
        $pdfHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $filename . '</title>
            <style>
                @media print {
                    body {
                        font-family: Arial, sans-serif;
                        color: #000;
                        margin: 0;
                        padding: 20px;
                    }

                    .page-break {
                        page-break-after: always;
                    }

                    a {
                        text-decoration: none;
                        color: #000;
                    }

                    .no-print {
                        display: none;
                    }
                }

                /* Styles pour l\'affichage à l\'écran */
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }

                .print-button {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 10px 20px;
                    background-color: #6a11cb;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    z-index: 1000;
                }

                .back-button {
                    position: fixed;
                    top: 20px;
                    left: 20px;
                    padding: 10px 20px;
                    background-color: #333;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    z-index: 1000;
                    text-decoration: none;
                }

                @media print {
                    .print-button, .back-button {
                        display: none;
                    }
                }
            </style>
            <script>
                window.onload = function() {
                    // Ajouter un bouton d\'impression
                    var printButton = document.createElement("button");
                    printButton.className = "print-button no-print";
                    printButton.innerHTML = "Imprimer";
                    printButton.onclick = function() {
                        window.print();
                    };
                    document.body.appendChild(printButton);

                    // Ajouter un bouton de retour
                    var backButton = document.createElement("a");
                    backButton.className = "back-button no-print";
                    backButton.innerHTML = "Retour aux avis";
                    backButton.href = "/avis";
                    document.body.appendChild(backButton);
                };
            </script>
        </head>
        <body>' . $html . '</body>
        </html>';

        $response->setContent($pdfHtml);

        return $response;
    }
}
