<?php

namespace App\Command;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'embedding:generate',
    description: 'Génère les embeddings de vos données',
)]
class GenerateEmbeddingsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Embeddings de vos données.');

        $io->section('Lecture des données');
        $dataReader = new FileDataReader(__DIR__.'/../../public/symfonyBaseDoc/6.4/best_practices.rst');
        $documents = $dataReader->getDocuments();
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
        $vectorStore = new FileSystemVectorStore();
        $vectorStore->addDocuments($embeddedDocuments);
        $io->success('Les embeddings ont été sauvegardés avec succès.');

        $io->success(
            'Les embeddings ont été générés avec succès et stockés dans le fichier documents-vectorStore.json'
        );

        return Command::SUCCESS;
    }
}
