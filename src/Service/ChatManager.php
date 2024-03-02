<?php

namespace App\Service;

use App\Entity\Embedding;
use Doctrine\ORM\EntityManagerInterface;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

class ChatManager
{
    public const FILTER_VERSION = 'version';

    private OpenAIConfig $openAIConfig;
    private QuestionAnswering $questionAnswering;

    public function __construct(
        private string $openAiApiKey,
        private EntityManagerInterface $entityManager,
    ) {
        $this->openAIConfig = new OpenAIConfig();
        $this->openAIConfig->apiKey = $this->openAiApiKey;
        // Setting up the question answering service
        $this->questionAnswering = new QuestionAnswering(
            new DoctrineVectorStore($this->entityManager, Embedding::class),
            new OpenAIEmbeddingGenerator($this->openAIConfig),
            new OpenAIChat($this->openAIConfig)
        );
    }

    public function generateAnswer(
        string $question,
        string $version = '6.4'
    ): string {
        return $this->questionAnswering->answerQuestion($question, 4, [
            self::FILTER_VERSION => $version,
        ]);
    }
}
