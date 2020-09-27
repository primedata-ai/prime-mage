<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Console\Command;

use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\ReplyExtension;
use Enqueue\Consumption\QueueConsumer;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Model\Jobs\ConsumePrimeEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrimeSync extends Command
{
    const TOPIC = 'primedata-events';

    /**
     * @var SyncHandle
     */
    private $syncHandle;
    /**
     * @var \Prime\Client
     */
    private $client;
    /**
     * @var \Interop\Queue\Context
     */
    private $context;

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
        $this->client = $syncHandle->getPrimeClient();
        $this->context = $syncHandle->getContext();
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueConsumer = new QueueConsumer($this->context, new ChainExtension([
            new ReplyExtension()
        ]));

        $queueConsumer->bind(self::TOPIC, new ConsumePrimeEvent($this->client));
        $queueConsumer->consume();
    }
}
