<?php

namespace App\Command;

use App\Service\Document\DocManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'doc:download',
    description: 'Download the documentation from Github',
)]
class DocDownloadCommand extends Command
{
    public function __construct(
        private DocManager $docManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('branch', InputArgument::OPTIONAL, 'Github branch')
            ->addArgument('user', InputArgument::OPTIONAL, 'Github user')
            ->addArgument('repository', InputArgument::OPTIONAL, 'Github repository')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $input->getArgument('user');
        $repository = $input->getArgument('repository');
        $branch = $input->getArgument('branch');

        if (!$user) {
            $user = DocManager::SYMFONY_USER;
        }

        if (!$repository) {
            $repository = DocManager::SYMFONY_REPOSITORY;
        }

        if (!$branch) {
            $branch = '6.4';
        }

        try {
            $this->docManager->downloadDoc(
                $user,
                $repository,
                $branch,
            );
        } catch (\Throwable $th) {
            $io->error($th->getMessage());

            return Command::FAILURE;
        }

        $io->success('Documentation has been downloaded in public/'.$user.'-'.$repository.'/'.$branch.'/ directory.');

        return Command::SUCCESS;
    }
}
