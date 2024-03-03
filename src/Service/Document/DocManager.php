<?php

namespace App\Service\Document;

use App\Entity\Embedding;
use App\Exception\Repository\BranchNotFoundException;
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
    public const EXTENSION_RST = '.rst';
    public const TYPE_FILE = 'blob';
    public const DIRECTORIES_TO_IGNORE = ['.github', '_build', '_images', '_includes', 'contributing'];

    public const SYMFONY_USER = 'symfony';
    public const SYMFONY_REPOSITORY = 'symfony-docs';
    public const FILE_RIGHTS = 0775;
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
        private string $tempDownloadPath,
        private string $docPath,
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private ZipDocumentManager $zipDocumentManager,
    ) {
    }

    public function getDocPath(
        string $user = self::SYMFONY_USER,
        string $repository = self::SYMFONY_REPOSITORY,
        string $branch = '6.4'
    ): string {
        return $this->docPath.$user.'-'.$repository.'/'.$branch.'/';
    }

    private function createFolderIfNotExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, self::FILE_RIGHTS, true);
        }
    }

    public function downloadZipRepository(
        string $user,
        string $repository,
        string $branch,
    ): void {
        try {
            $fileContent = $this->httpClient->request(
                self::API_GET_METHOD,
                "https://codeload.github.com/$user/$repository/zip/$branch"
            )->getContent();
        } catch (\Throwable $th) {
            throw new BranchNotFoundException($branch);
        }

        // Create the directory if it does not exist
        $this->createFolderIfNotExists($this->tempDownloadPath);

        // Write the file
        file_put_contents($this->tempDownloadPath."$user-$repository-$branch.zip", $fileContent);
    }

    public function moveFilesInPublic(
        string $user,
        string $repository,
        string $branch,
    ): void {
        // Create the directory if it does not exist
        $localPath = $this->getDocPath($user, $repository, $branch);
        $this->createFolderIfNotExists($localPath);

        // Find files in temp path to move in the public directory when the extension is .rst
        $finder = new Finder();
        $files = $finder
            ->in($this->tempDownloadPath."$repository-$branch")
            ->files()
            ->name('*.rst')
            ->sortByName()
        ;

        foreach ($files as $file) {
            $filePath = $localPath.$file->getRelativePathname();
            $directory = dirname($filePath);
            $this->createFolderIfNotExists($directory);
            copy($file->getRealPath(), $filePath);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->deleteDirectory("$path/$file") : unlink("$path/$file");
        }

        rmdir($path);
    }

    public function cleanTempDirectory(
        string $user,
        string $repository,
        string $branch,
    ): void {
        $zipPath = $this->tempDownloadPath."$user-$repository-$branch.zip";
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $localPath = $this->tempDownloadPath."$repository-$branch";
        $this->deleteDirectory($localPath);
    }

    public function downloadDoc(
        string $user,
        string $repository,
        string $branch,
    ): void {
        try {
            $this->downloadZipRepository($user, $repository, $branch);
            $this->zipDocumentManager->unzipRepository($user, $repository, $branch);
            $this->moveFilesInPublic($user, $repository, $branch);
            $this->cleanTempDirectory($user, $repository, $branch);
        } catch (\Throwable $th) {
            $this->cleanTempDirectory($user, $repository, $branch);
            throw $th;
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
        return DocumentSplitter::splitDocuments($documents, $maxLength);
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
        return new DoctrineVectorStore($this->entityManager, Embedding::class);
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
