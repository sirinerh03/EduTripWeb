<?php
// Script pour vérifier les clés reCAPTCHA

// Charger l'autoloader de Composer
require __DIR__.'/vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = new \Symfony\Component\Dotenv\Dotenv();
try {
    $dotenv->loadEnv(__DIR__.'/.env');
    
    echo "Clés reCAPTCHA :\n";
    echo "RECAPTCHA_SITE_KEY: " . ($_ENV['RECAPTCHA_SITE_KEY'] ?? 'Non définie') . "\n";
    echo "RECAPTCHA_SECRET_KEY: " . ($_ENV['RECAPTCHA_SECRET_KEY'] ?? 'Non définie') . "\n";
} catch (Exception $e) {
    echo "Erreur lors du chargement des variables d'environnement : " . $e->getMessage() . "\n";
}
