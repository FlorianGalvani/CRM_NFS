<?php

namespace App\Command\Common;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ExecuteCommandTrait
{
    protected function executeCommands(array $commands, InputInterface $input, OutputInterface $output): int
    {
        foreach ($commands as $command) {
            $command = (array) $command;
            list($name, $parameters) = array_pad($command, 2, null);
            $res = $this->executeCommand($name, $parameters ?: $input, $output);
            if (0 !== $res) {
                exit;
            }
        }

        return Command::SUCCESS;
    }

    protected function executeCommand(string $name, InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find($name);

        return $command->run($input, $output);
    }
}