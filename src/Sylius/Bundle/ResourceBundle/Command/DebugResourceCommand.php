<?php

namespace Sylius\Bundle\ResourceBundle\Command;

use Sylius\Component\Resource\Metadata\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class DebugResourceCommand extends Command
{
    private $registry;

    public function __construct(Registry $registry)
    {
        parent::__construct();
        $this->registry = $registry;
    }

    public function configure()
    {
        $this->setName('sylius:debug:resource');
        $this->addArgument('alias', InputArgument::OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $alias = $input->getArgument('alias');

        if (null === $alias) {
            return $this->listAll($output);
        }

        $metadata = $this->registry->get($alias);

        $table = new Table($output);
        $table->addRow([
            sprintf('<info>%s</info>', 'alias'),
            $metadata->getAlias(),
        ]);
        $rows = [];
        $rows[] = [ 'Alias', $metadata->getAlias() ];

        $rows = $this->buildParameters($metadata->getParameters(), $rows);
        $table->setStyle('compact');
        $table->render();
    }

    private function buildParameters(array $parameters, array $rows, $level = 0)
    {

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $rows = $this->buildParameters($value, $rows, $level + 1);
                continue;
            }

            $rows[] = [ $key, str_repeat(' ', $level) . $value ];
        }

        return $rows;
    }

    private function listAll(OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Alias',
            'Driver',
        ]);

        foreach ($this->registry->getAll() as $metadata) {
            $table->addRow([
                $metadata->getAlias(),
                $metadata->getDriver(),
            ]);
        }

        $table->render();
    }
}
