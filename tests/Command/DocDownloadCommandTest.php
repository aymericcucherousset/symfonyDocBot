<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DocDownloadCommandTest extends KernelTestCase
{
    public static function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? self::removeDirectory("$directory/$file") : unlink("$directory/$file");
        }
        rmdir($directory);
    }

    public function testDownloadDocNotExisting(): void
    {
        // Remove public folder if exists
        self::removeDirectory(__DIR__.'/../../public/symfony-symfony-docs/6.4');

        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('doc:download');
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

        $command = $application->find('doc:download');
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
}
