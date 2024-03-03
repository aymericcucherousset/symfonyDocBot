<?php

namespace App\Command;

ini_set('memory_limit', '-1');

use App\Service\Document\DocManager;
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

        $io->section('Symfony version check');
        if (!DocManager::checkSymfonyVersion($symfonyVersion)) {
            $io->error(
                'The Symfony version must be one of the following : '.implode(', ', DocManager::SYMFONY_VERSIONS)
            );

            return Command::FAILURE;
        }

        $io->title('Embeddings of your data.');

        $io->section('Reading data');
        $documents = DocManager::getDocuments(
            self::DOC_PATH."$user-$repository/$symfonyVersion/"
        );

        $io->success('The data was successfully read, and'.count($documents).' documents were found.');

        $io->section('Splitting documents');
        $splittedDocuments = $this->docManager->splitDocuments($documents);

        $io->success(
            'The documents were successfully split into '.count($splittedDocuments).' documents of 500 words maximum.'
        );

        $io->section('Embedding generation');
        $progressBar = $io->createProgressBar(count($splittedDocuments));
        $progressBar->start();

        $embeddedDocuments = [];

        foreach ($splittedDocuments as $splittedDocument) {
            $embeddedDocuments[] = $this->docManager->generateEmbedding($splittedDocument);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success('The embeddings were successfully generated.');

        $io->section('Saving embeddings.');
        $progressBar = $io->createProgressBar(count($embeddedDocuments));
        $progressBar->start();

        $vectorStore = $this->docManager->getVectorStore();
        foreach ($embeddedDocuments as $embeddedDocument) {
            $this->docManager->saveEmbedding($embeddedDocument, $vectorStore, $symfonyVersion);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success('The embeddings have been successfully saved.');

        $io->success(
            'The embeddings were successfully generated and stored in the database.'
        );

        return Command::SUCCESS;
    }
}
