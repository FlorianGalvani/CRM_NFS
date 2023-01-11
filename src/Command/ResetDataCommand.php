<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $commandInput = new ArrayInput([]);
        $commands = [
            'doctrine:database:drop --force', // --force ?? // ne fonctionne pas
            'doctrine:database:create',
            'doctrine:migrations:migrate',
            'doctrine:fixtures:load'
        ];

        $this->executeCommands($commands, $commandInput, $output);

        $io->success("Database reset completed");

        return Command::SUCCESS;
    }
}