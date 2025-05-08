<?php
namespace App\Command;

use App\Service\HuggingFaceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:huggingface-chat',
    description: 'Chat with Hugging Face AI'
)]
class HuggingFaceChatCommand extends Command
{
    public function __construct(
        private HuggingFaceService $huggingFace
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        while (true) {
            $prompt = $io->ask('You (type "exit" to quit)');

            if ($prompt === null || strtolower($prompt) === 'exit') {
                break;
            }

            $response = $this->huggingFace->generateResponse(
                "You are a travel assistant. Respond in English clearly and helpfully. The user asks: {$prompt}"
            );
            $io->writeln("AI: " . $response);
        }

        return Command::SUCCESS;
    }
}