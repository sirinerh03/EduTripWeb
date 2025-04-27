<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateRepositoriesCommand extends Command
{
    protected static $defaultName = 'app:generate-repositories';

    protected static $defaultDescription = 'Generates repository classes for all entities.';

    private Filesystem $filesystem;
    private Finder $finder;


    public function __construct(Filesystem $filesystem, Finder $finder)
    {
        parent::__construct();



        $this->filesystem = $filesystem;
        $this->finder = $finder;
    }

    protected function configure()
    {

        $this->setHelp('This command will generate repository classes for all entities in src/Entity.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Generating repositories for all entities...');

        // Configurer le Finder pour trouver les entités
        $this->finder->files()
            ->in('src/Entity')
            ->name('*.php')
            ->depth(0); // Seulement les fichiers directement dans le dossier Entity

        foreach ($this->finder as $file) {
            $entityClass = $file->getBasename('.php');
            $this->generateRepository($entityClass, $output);

        }

        $output->writeln('Repository generation complete!');
        return Command::SUCCESS;
    }


    private function generateRepository(string $entityClass, OutputInterface $output): void
    {
        $repositoryClass = $entityClass . 'Repository';
        $repositoryPath = 'src/Repository/' . $repositoryClass . '.php';

        if ($this->filesystem->exists($repositoryPath)) {
            $output->writeln("Repository already exists for: {$entityClass}");
            return;
        }

        $repositoryCode = <<<PHP
<?php

namespace App\Repository;

use App\Entity\\{$entityClass};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class {$repositoryClass} extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry \$registry)
    {
        parent::__construct(\$registry, {$entityClass}::class);
    }

    // Add your custom repository methods here
}
PHP;

        $this->filesystem->dumpFile($repositoryPath, $repositoryCode);
        $output->writeln("Generated repository: {$repositoryClass}");
    }
}