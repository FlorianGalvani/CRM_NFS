<?php

namespace App\Command;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function in_array;
use function method_exists;
use function sprintf;

#[AsCommand(
    name: 'app:drop-db',
    description: 'Execute les commandes Symfony pour supprimer la base de donnée, la recréer et lance les migrations et les fixtures'
)]
class DropDatabaseCommand extends Command
{
    public const RETURN_CODE_NOT_DROP = 1;

    public const RETURN_CODE_NO_FORCE = 2;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Drops the configured database')
            ->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'The connection to use for this command')
            ->addOption('if-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the database doesn\'t exist')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Set this parameter to execute this action');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionName = $input->getOption('connection');

        $connection = $this->em->getConnection();

        $ifExists = $input->getOption('if-exists');

        $params = $connection->getParams();

        if (isset($params['primary'])) {
            $params = $params['primary'];
        }

        $name = $params['path'] ?? ($params['dbname'] ?? false);
        if (! $name) {
            throw new InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
        }

        /** @psalm-suppress InvalidArrayOffset */
        unset($params['dbname'], $params['url']);

        // Reopen connection without database name set
        // as some vendors do not allow dropping the database connected to.
        $connection->close();
        $connection         = DriverManager::getConnection($params);
        $schemaManager      = method_exists($connection, 'createSchemaManager')
            ? $connection->createSchemaManager()
            : $connection->getSchemaManager();
        $shouldDropDatabase = ! $ifExists || in_array($name, $schemaManager->listDatabases());

        // Only quote if we don't have a path
        if (! isset($params['path'])) {
            $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($name);
        }

        try {
            if ($shouldDropDatabase) {
                $schemaManager->dropDatabase($name);
                $output->writeln(sprintf('<info>Dropped database <comment>%s</comment> for connection named <comment>%s</comment></info>', $name, $connectionName));
            } else {
                $output->writeln(sprintf('<info>Database <comment>%s</comment> for connection named <comment>%s</comment> doesn\'t exist. Skipped.</info>', $name, $connectionName));
            }

            return 0;
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Could not drop database <comment>%s</comment> for connection named <comment>%s</comment></error>', $name, $connectionName));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return self::RETURN_CODE_NOT_DROP;
        }
    }
}
