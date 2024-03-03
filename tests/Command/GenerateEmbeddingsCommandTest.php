<?php

namespace App\Tests\Command;

use App\Entity\Embedding;
use App\Service\Document\DocManager;
use Doctrine\ORM\EntityManagerInterface;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateEmbeddingsCommandTest extends KernelTestCase
{
    public function testGenerateEmbeddings(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        // Mock generateEmbedding method from DocManager
        $docManager = $this->createMock(DocManager::class);

        $docManager->method('generateEmbedding')
            ->willReturn(new Document())
        ;

        $docManager->method('getVectorStore')
            ->willReturn(new DoctrineVectorStore(
                static::getContainer()->get(EntityManagerInterface::class),
                Embedding::class
            ));

        self::$kernel->getContainer()->set(DocManager::class, $docManager);

        $command = $application->find('embedding:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'version' => '5.4',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString(
            'The data was successfully read, and',
            $output
        );

        $this->assertStringContainsString(
            'Saving embeddings.',
            $output
        );

        $this->assertStringContainsString(
            'The embeddings were successfully generated and stored in the database.',
            $output
        );
    }
}
