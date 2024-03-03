<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DocDownloadCommandTest extends KernelTestCase
{
    private const COMMAND = 'doc:download';

    public static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $directory.'/'.$file;
            (is_dir($filePath)) ? self::removeDirectory($filePath) : unlink($filePath);
        }
        rmdir($directory);
    }

    public function testDownloadDocNotExistingInLocalFolders(): void
    {
        // Remove public folder if exists
        self::removeDirectory(__DIR__.'/../../public/symfony-symfony-docs/6.4');

        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString(
            'Documentation has been downloaded in public/symfony-symfony-docs/6.4/',
            $output
        );
    }

    public function testDownloadDocAlreadyDownloaded(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'branch' => '6.4',
            'user' => 'symfony',
            'repository' => 'symfony-docs',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString(
            'Documentation has been downloaded in public/symfony-symfony-docs/6.4/',
            $output
        );
    }

    public function testDownloadDocWithNonExistingBranch(): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'branch' => 'non-existing-branch',
            'user' => 'symfony',
            'repository' => 'symfony-docs',
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay(true);
        $this->assertStringContainsString(
            'Branch non-existing-branch not found',
            $output
        );
    }
}
