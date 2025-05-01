<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CheckRecaptchaCommand extends Command
{
    protected static $defaultName = 'app:check-recaptcha';
    
    private $params;
    
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setDescription('Vérifie les clés reCAPTCHA');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Vérification des clés reCAPTCHA...');
        
        $siteKey = $this->params->get('recaptcha.site_key');
        $secretKey = $this->params->get('recaptcha.secret_key');
        
        $output->writeln('Site key: ' . $siteKey);
        $output->writeln('Secret key: ' . $secretKey);
        
        return Command::SUCCESS;
    }
}
