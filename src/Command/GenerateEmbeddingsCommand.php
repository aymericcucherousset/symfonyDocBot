<?php

namespace App\Command;

ini_set('memory_limit', '-1');

use App\Entity\Embedding;
use App\Service\DocManager;
use Doctrine\ORM\EntityManagerInterface;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'embedding:generate',
    description: 'Génère les embeddings de vos données',
)]
class GenerateEmbeddingsCommand extends Command
{
    private const DOC_PATH = __DIR__.'/../../public/';

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::REQUIRED, 'Symfony version')
            ->addArgument('user', InputArgument::OPTIONAL, 'Github user')
            ->addArgument('repository', InputArgument::OPTIONAL, 'Github repository')
        ;
    }

    /**
     * @return string[]
     */
    private function getSubDirectories(string $path): array
    {
        // Create an array of all subdirectories
        $finder = new Finder();
        $directories = $finder
            ->in($path)
            ->directories()
            ->sortByName()
        ;

        $directoriesArray = [];
        foreach ($directories as $directory) {
            $directoriesArray[] = $directory->getRelativePathname();
        }

        return $directoriesArray;
    }

    /**
     * @return Document[]
     */
    private function getDocuments(string $path): array
    {
        // Get all subdirectories in public/[user]-[repository]/[version] directory
        $documents = [];

        foreach ($this->getSubDirectories($path) as $directory) {
            $dataReader = new FileDataReader(
                "$path/$directory",
                Embedding::class
            );
            $documents = array_merge($documents, $dataReader->getDocuments());
        }

        return $documents;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $input->getArgument('user');
        $repository = $input->getArgument('repository');
        $symfonyVersion = $input->getArgument('version');

        if (!$user) {
            $user = DocManager::SYMFONY_USER;
        }

        if (!$repository) {
            $repository = DocManager::SYMFONY_REPOSITORY;
        }

        $io->section('Vérification de la version de Symfony');
        if (!DocManager::checkSymfonyVersion($symfonyVersion)) {
            $io->error(
                'La version de Symfony doit être l\'une des suivantes : '.implode(', ', DocManager::SYMFONY_VERSIONS)
            );

            return Command::FAILURE;
        }

        $io->title('Embeddings de vos données.');

        $io->section('Lecture des données');
        $documents = $this->getDocuments(
            self::DOC_PATH."$user-$repository/$symfonyVersion/"
        );

        $io->success('Les données ont été lues avec succès, et '.count($documents).' documents ont été trouvés.');

        $io->section('Découpage des documents');
        $splittedDocuments = DocumentSplitter::splitDocuments($documents, 500);
        $io->success(
            'Les documents ont été découpés avec succès en '.count($splittedDocuments).' documents de 500 mots maximum.'
        );

        $io->section('Génération des embeddings');
        $embeddingGenerator = new OpenAIEmbeddingGenerator();
        $embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);
        $io->success('Les embeddings ont été générés avec succès.');

        $io->section('Sauvegarde des embeddings');
        $vectorStore = new DoctrineVectorStore($this->entityManager, Embedding::class);
        $vectorStore->addDocuments($embeddedDocuments);
        $io->success('Les embeddings ont été sauvegardés avec succès.');

        $io->success(
            'Les embeddings ont été générés avec succès et stockés en base de données.'
        );

        return Command::SUCCESS;
    }
}
