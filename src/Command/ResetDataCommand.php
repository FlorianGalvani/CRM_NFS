<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Command\Common\ExecuteCommandTrait;

#[AsCommand(
    name: 'app:reset-data',
    description: 'Execute les commandes Symfony pour supprimer la base de donnée, la recréer et lance les migrations et les fixtures'
)]
class ResetDataCommand extends Command
{
    use ExecuteCommandTrait;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE);
        $this->addOption('new-migration', 'nm', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force') && !$io->confirm("Do you want to <comment>purge</comment> this app ? All stored data, files, (etc.) will be deleted", false)) {
            $io->writeln("Purge <comment>aborted</comment>");

            return Command::SUCCESS;
        }

        $commandInput = new ArrayInput([]);
        $commands = [
            'app:drop-db',
            'doctrine:database:create'
        ];

        if ($input->getOption('new-migration')) {
            $commands[] = 'make:migration';
        }

        $commands[] = 'doctrine:migrations:migrate';
        $commands[] = 'doctrine:fixtures:load';

        $this->executeCommands($commands, $commandInput, $output);

        $io->success("Database reset completed");

        return Command::SUCCESS;
    }
}