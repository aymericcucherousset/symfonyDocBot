<?php

namespace App\Service;

use App\Entity\Embedding;
use App\Exception\GithubApiException;
use Doctrine\ORM\EntityManagerInterface;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DocManager
{
    private string $publicPath = __DIR__.'/../../public/';
    public const EXTENSION_RST = '.rst';
    public const TYPE_FILE = 'blob';
    public const DIRECTORIES_TO_IGNORE = ['.github', '_build', '_images', '_includes', 'contributing'];

    public const SYMFONY_USER = 'symfony';
    public const SYMFONY_REPOSITORY = 'symfony-docs';
    private const FILE_RIGHTS = 0775;
    private const API_GET_METHOD = 'GET';
    public const SYMFONY_VERSIONS = [
        '2.0',
        '2.1',
        '2.2',
        '2.3',
        '2.4',
        '2.5',
        '2.6',
        '2.7',
        '2.8',
        '3.0',
        '3.1',
        '3.2',
        '3.3',
        '3.4',
        '4.0',
        '4.1',
        '4.2',
        '4.3',
        '4.4',
        '5.0',
        '5.1',
        '5.2',
        '5.3',
        '5.4',
        '6.0',
        '6.1',
        '6.2',
        '6.3',
        '6.4',
        '7.0',
        '7.1',
    ];

    public static function checkSymfonyVersion(string $version): bool
    {
        return in_array($version, self::SYMFONY_VERSIONS);
    }

    /**
     * @return string[]
     */
    public static function getSubDirectories(string $path): array
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
    public static function getDocuments(string $path): array
    {
        $dataReader = new FileDataReader(
            "$path",
            Embedding::class
        );
        $documents = $dataReader->getDocuments();

        foreach (self::getSubDirectories($path) as $directory) {
            $dataReader = new FileDataReader(
                "$path/$directory",
                Embedding::class
            );
            $documents = array_merge($documents, $dataReader->getDocuments());
        }

        return $documents;
    }

    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getDocPath(
        string $user = self::SYMFONY_USER,
        string $repository = self::SYMFONY_REPOSITORY,
    ): string {
        return $this->publicPath.$user.'-'.$repository.'/';
    }

    private function isFileWithExtension(
        string $file,
        string $extension = self::EXTENSION_RST
    ): bool {
        return false !== strpos($file, $extension);
    }

    private function isFile(string $type): bool
    {
        return self::TYPE_FILE === $type;
    }

    /**
     * @param string[] $directories
     */
    private function fileIsNotInDirectories(
        string $file,
        array $directories
    ): bool {
        foreach ($directories as $directory) {
            if (false !== strpos($file, $directory)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getDocFilesInRepository(
        string $githubUser,
        string $githubRepository,
        string $branch,
    ): array {
        // make a request to the Github API url
        // https://api.github.com/repos/[USER]/[REPO]/git/trees/[BRANCH]?recursive=1

        $response = $this->httpClient->request(
            self::API_GET_METHOD,
            "https://api.github.com/repos/$githubUser/$githubRepository/git/trees/$branch?recursive=1"
        );

        if (200 !== $response->getStatusCode()
            || 'application/json; charset=utf-8' !== $response->getHeaders()['content-type'][0]
        ) {
            throw new GithubApiException('Error while getting files from Github API');
        }

        // Parse the response to get only the files
        /** @var array{
         *      int,
         *      array{path: string, mode: string, type: string, sha: string, size: int, url: string}
         *  } $content */
        $content = $response->toArray()['tree'];
        $files = [];
        foreach ($content as $file) {
            if ($this->isFile($file['type'])
                && $this->isFileWithExtension($file['path'], self::EXTENSION_RST)
                && $this->fileIsNotInDirectories($file['path'], self::DIRECTORIES_TO_IGNORE)
            ) {
                $files[] = $file['path'];
            }
        }

        return $files;
    }

    private function createFolderIfNotExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, self::FILE_RIGHTS, true);
        }
    }

    public function downloadDoc(
        string $githubUser,
        string $githubRepository,
        string $branch,
        int $delayAgainstDDOS = 1
    ): void {
        $files = $this->getDocFilesInRepository($githubUser, $githubRepository, $branch);
        $localPath = $this->getDocPath($githubUser, $githubRepository);

        // Download the files in the local path
        // https://raw.githubusercontent.com/[USER]/[REPO]/[BRANCH]/[PATH]
        // public/symfony-symfony-docs/[BRANCH]/[FILE]
        foreach ($files as $file) {
            $fileContent = $this->httpClient->request(
                self::API_GET_METHOD,
                "https://raw.githubusercontent.com/$githubUser/$githubRepository/$branch/$file"
            )->getContent();

            // Create the directory if it does not exist
            $filePath = $localPath."$branch/".$file;
            $directory = dirname($filePath);
            $this->createFolderIfNotExists($directory);

            // Write the file
            file_put_contents($filePath, "version: $branch\n".$fileContent);
            sleep($delayAgainstDDOS);
        }
    }

    /**
     * @param Document[] $documents
     *
     * @return Document[]
     */
    public function splitDocuments(
        array $documents,
        int $maxLength = 500
    ): array {
        return DocumentSplitter::splitDocuments(
            $documents,
            $maxLength
        );
    }

    public function generateEmbedding(Document $document): Document
    {
        $embeddingGenerator = new OpenAIEmbeddingGenerator();

        return $embeddingGenerator->embedDocument($document);
    }

    /**
     * @param Document[] $splittedDocuments
     *
     * @return Document[]
     */
    public function generateEmbeddings(array $splittedDocuments): array
    {
        $embeddingGenerator = new OpenAIEmbeddingGenerator();

        return $embeddingGenerator->embedDocuments($splittedDocuments);
    }

    public function getVectorStore(): DoctrineVectorStore
    {
        return new DoctrineVectorStore(
            $this->entityManager,
            Embedding::class
        );
    }

    public function saveEmbedding(
        Document $document,
        DoctrineVectorStore $vectorStore,
        string $version,
    ): void {
        // Convert the document to an embedding entity
        // Save the embedding in the database with the symfony version
        $embedding = new Embedding();
        $embedding->content = $document->content;
        $embedding->type = $document->sourceType;
        $embedding->version = $document->sourceName;
        $embedding->embedding = $document->embedding;
        $embedding->hash = $document->hash;
        $embedding->chunkNumber = $document->chunkNumber;
        $embedding->version = $version;

        $vectorStore->addDocument($embedding);
    }

    /**
     * @param Document[] $embeddedDocuments
     */
    public function saveEmbeddings(array $embeddedDocuments): void
    {
        $vectorStore = new DoctrineVectorStore(
            $this->entityManager,
            Embedding::class
        );
        $vectorStore->addDocuments($embeddedDocuments);
    }
}
