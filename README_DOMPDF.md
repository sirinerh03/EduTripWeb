# Implémentation du bundle Nucleos DomPDF dans EduTrip

Ce document explique comment le bundle Nucleos DomPDF a été intégré dans l'application EduTrip pour la génération de PDF.

## Installation

Le bundle a été installé via Composer :

```bash
composer require nucleos/dompdf-bundle
```

## Configuration

Le bundle est configuré dans le fichier `config/packages/nucleos_dompdf.yaml` :

```yaml
nucleos_dompdf:
    defaults:
        chroot: '%kernel.project_dir%/public/assets'
        logOutputFile: ''
```

## Structure

### Service DompdfService

Un nouveau service `DompdfService` a été créé pour encapsuler la logique de génération de PDF en utilisant le bundle Nucleos DomPDF :

- Localisation : `src/Service/DompdfService.php`
- Dépendances :
  - `Nucleos\DompdfBundle\Wrapper\DompdfWrapper` : Le wrapper fourni par le bundle
  - `Twig\Environment` : Pour le rendu des templates
- Fonctionnalités :
  - `generatePdf()` : Génère un PDF à partir d'un template Twig et l'affiche dans le navigateur
  - `downloadPdf()` : Génère un PDF à partir d'un template Twig et le propose en téléchargement

### Contrôleurs

Les contrôleurs suivants ont été mis à jour pour utiliser le nouveau service :

- `RewardController` : Génère des PDF pour les récompenses de type SpinReward
- `PdfController` : Génère des PDF pour les récompenses de type Reward

Un contrôleur de test a également été créé :

- `TestPdfController` : Permet de tester la génération de PDF avec une route dédiée `/test/pdf`

### Templates

Les templates suivants sont utilisés pour la génération de PDF :

- `templates/pdf/reward.html.twig` : Template pour les récompenses
- `templates/test/pdf_test.html.twig` : Template de test

## Utilisation

Pour générer un PDF, injectez le service `DompdfService` dans votre contrôleur et appelez la méthode `generatePdf()` :

```php
public function generatePdf(
    DompdfService $dompdfService
): Response
{
    return $dompdfService->generatePdf(
        'template/path.html.twig',
        [
            'data' => $data,
        ],
        'filename.pdf'
    );
}
```

## Personnalisation

Pour personnaliser les PDF générés, vous pouvez :

1. Modifier les templates Twig
2. Ajuster les options du bundle dans le fichier `config/packages/nucleos_dompdf.yaml`
3. Configurer des options supplémentaires via le service `DompdfWrapper`

### Options disponibles dans le bundle

```yaml
nucleos_dompdf:
    defaults:
        # Répertoire racine pour les ressources locales
        chroot: '%kernel.project_dir%/public/assets'

        # Désactiver la création de fichiers log
        logOutputFile: ''

        # Autres options disponibles
        # defaultFont: 'Arial'
        # defaultMediaType: 'screen'
        # defaultPaperSize: 'A4'
        # defaultPaperOrientation: 'portrait'
        # isRemoteEnabled: true
```

## Compatibilité

DomPDF prend en charge la plupart des fonctionnalités CSS, mais certaines limitations existent :

- Les gradients CSS complexes ne sont pas bien supportés (utiliser des couleurs simples)
- Certaines fonctionnalités CSS3 avancées peuvent ne pas fonctionner
- Les polices personnalisées doivent être correctement configurées
- JavaScript n'est pas supporté

## Test

Pour tester la génération de PDF, accédez à la route `/test/pdf` qui affichera un PDF de test.
