<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Console\Command;

use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrimeSync extends Command
{

    /**
     * @var SyncHandle
     */
    private $syncHandle;

    /**
     * PrimeSync constructor.
     * @param SyncHandle $syncHandle
     * @param string|null $name
     * @codeCoverageIgnore
     */
    public function __construct(
        SyncHandle $syncHandle,
        string $name = null
    ) {
        $this->syncHandle = $syncHandle;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('prime:sync');
        $this->setDescription('this command get message from queue and sync to primeAi');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $this->syncHandle->sendDataToPrime();
        }
    }
}
