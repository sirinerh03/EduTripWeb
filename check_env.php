<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

echo "RECAPTCHA_SITE_KEY: " . $_ENV['RECAPTCHA_SITE_KEY'] . "\n";
echo "RECAPTCHA_SECRET_KEY: " . $_ENV['RECAPTCHA_SECRET_KEY'] . "\n";
