<?php

namespace App\Service;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

class ChatManager
{
    private OpenAIConfig $openAIConfig;

    public function __construct(private string $openAiApiKey)
    {
        $this->openAIConfig = new OpenAIConfig();
        $this->openAIConfig->apiKey = $this->openAiApiKey;
    }

    public function generateAnswer(string $question): string
    {
        $vectorStore = new FileSystemVectorStore('../documents-vectorStore.json');
        $embeddingGenerator = new OpenAIEmbeddingGenerator($this->openAIConfig);

        $qa = new QuestionAnswering(
            $vectorStore,
            $embeddingGenerator,
            new OpenAIChat($this->openAIConfig)
        );

        return $qa->answerQuestion($question);
    }
}
