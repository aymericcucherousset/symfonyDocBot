<?php

namespace App\Command;

ini_set('memory_limit', '-1');

use App\Service\DocManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'embedding:generate',
    description: 'Génère les embeddings de vos données',
)]
class GenerateEmbeddingsCommand extends Command
{
    private const DOC_PATH = __DIR__.'/../../public/';

    public function __construct(
        private DocManager $docManager,
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
        $documents = DocManager::getDocuments(
            self::DOC_PATH."$user-$repository/$symfonyVersion/"
        );

        $io->success('Les données ont été lues avec succès, et '.count($documents).' documents ont été trouvés.');

        $io->section('Découpage des documents');
        $splittedDocuments = $this->docManager->splitDocuments($documents);

        $io->success(
            'Les documents ont été découpés avec succès en '.count($splittedDocuments).' documents de 500 mots maximum.'
        );

        $io->section('Génération des embeddings');
        $progressBar = $io->createProgressBar(count($splittedDocuments));
        $progressBar->start();

        $embeddedDocuments = [];

        foreach ($splittedDocuments as $splittedDocument) {
            $embeddedDocuments[] = $this->docManager->generateEmbedding($splittedDocument);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success('Les embeddings ont été générés avec succès.');

        $io->section('Sauvegarde des embeddings');
        $progressBar = $io->createProgressBar(count($embeddedDocuments));
        $progressBar->start();

        $vectorStore = $this->docManager->getVectorStore();
        foreach ($embeddedDocuments as $embeddedDocument) {
            $this->docManager->saveEmbedding($embeddedDocument, $vectorStore, $symfonyVersion);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success('Les embeddings ont été sauvegardés avec succès.');

        $io->success(
            'Les embeddings ont été générés avec succès et stockés en base de données.'
        );

        return Command::SUCCESS;
    }
}
