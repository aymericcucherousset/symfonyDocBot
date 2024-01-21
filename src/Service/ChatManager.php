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
    private OpenAIConfig $openAIConfig;

    public function __construct(
        private string $openAiApiKey,
        private EntityManagerInterface $entityManager,
    ) {
        $this->openAIConfig = new OpenAIConfig();
        $this->openAIConfig->apiKey = $this->openAiApiKey;
    }

    public function generateAnswer(string $question): string
    {
        $vectorStore = new DoctrineVectorStore(
            $this->entityManager,
            Embedding::class,
        );

        $embeddingGenerator = new OpenAIEmbeddingGenerator($this->openAIConfig);

        $qa = new QuestionAnswering(
            $vectorStore,
            $embeddingGenerator,
            new OpenAIChat($this->openAIConfig)
        );

        return $qa->answerQuestion($question);
    }
}
