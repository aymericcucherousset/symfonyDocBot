<?php

namespace App\Service\Document;

use App\Exception\Document\Zip\ZipMaxRatioException;
use App\Exception\Document\Zip\ZipMaxSizeException;
use App\Exception\Document\Zip\ZipNavigationProtectionException;
use App\Exception\Document\Zip\ZipToManyFileException;

class ZipDocumentManager
{
    public const ZIP_MAX_FILES = 10000;
    public const ZIP_MAX_SIZE = 1000000000; // 1 GB
    public const ZIP_MAX_RATIO = 50;
    public const ZIP_READ_LENGTH = 1024;

    public function __construct(
        private string $tempDownloadPath,
    ) {
    }

    private function checkZipNavigationPayload(string $filename): void
    {
        if (false !== strpos($filename, '../') || '/' === substr($filename, 0, 1)) {
            throw new ZipNavigationProtectionException();
        }
    }

    private function checkZipMaxSize(int $totalSize): void
    {
        if ($totalSize > self::ZIP_MAX_SIZE) {
            throw new ZipMaxSizeException();
        }
    }

    private function checkZipRatio(int $currentSize, int $compSize): void
    {
        if ($compSize > 0) {
            $ratio = $currentSize / $compSize;
            if ($ratio > self::ZIP_MAX_RATIO) {
                throw new ZipMaxRatioException();
            }
        }
    }

    private function checkZipBomb(
        string $filename,
        int &$fileCount,
        \ZipArchive $zip,
        int &$totalSize,
        int $i,
    ): void {
        $stats = $zip->statIndex($i);
        if ('/' !== substr($filename, -1)) {
            ++$fileCount;
            if ($fileCount > self::ZIP_MAX_FILES) {
                throw new ZipToManyFileException();
            }

            $fp = $zip->getStream($filename);
            $currentSize = 0;
            while (!feof($fp)) {
                $currentSize += self::ZIP_READ_LENGTH;
                $totalSize += self::ZIP_READ_LENGTH;

                $this->checkZipMaxSize($totalSize);
                $this->checkZipRatio($currentSize, $stats['comp_size']);

                if (file_exists($this->tempDownloadPath.$filename)) {
                    unlink($this->tempDownloadPath.$filename);
                }
                file_put_contents($this->tempDownloadPath.$filename, fread($fp, self::ZIP_READ_LENGTH), FILE_APPEND);
            }
            fclose($fp);
        } else {
            mkdir($this->tempDownloadPath.$filename, DocManager::FILE_RIGHTS);
        }
    }

    public function unzipRepository(
        string $user,
        string $repository,
        string $branch,
    ): void {
        $fileCount = 0;
        $totalSize = 0;
        $file = $this->tempDownloadPath."$user-$repository-$branch.zip";

        $zip = new \ZipArchive();
        if (true === $zip->open($file)) {
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $filename = $zip->getNameIndex($i);

                $this->checkZipNavigationPayload($filename);
                $this->checkZipBomb($filename, $fileCount, $zip, $totalSize, $i);
            }
            $zip->close();
        }
    }
}
