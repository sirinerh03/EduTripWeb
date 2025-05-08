<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    #[Route('/switch-language/{_locale}', name: 'app_switch_language', requirements: ['_locale' => 'en|fr'])]
public function switchLanguage(Request $request, string $_locale): Response
{
    // 1. Stocker la locale en session
    $request->getSession()->set('_locale', $_locale);
    
    // 2. Récupérer l'URL de référence
    $referer = $request->headers->get('referer');
    
    if ($referer) {
        // 3. Remplacer la locale dans l'URL
        $newUrl = preg_replace(
            '~/(fr|en)(/|$)~i',
            '/'.$_locale.'$2',
            $referer
        );
        
        // 4. Rediriger vers la nouvelle URL
        return $this->redirect($newUrl);
    }
    
    // 5. Fallback vers la page d'accueil
    return $this->redirectToRoute('app_home', ['_locale' => $_locale]);
}}