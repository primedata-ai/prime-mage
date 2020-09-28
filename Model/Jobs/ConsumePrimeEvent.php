<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\Jobs;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Prime\Client as PrimeClientSdk;

class ConsumePrimeEvent implements Processor
{
    /**
     * @var PrimeClientSdk
     */
    protected $client;

    /**
     * ConsumePrimeEvent constructor.
     * @param PrimeClientSdk $client
     * @codeCoverageIgnore
     */
    public function __construct(
        PrimeClientSdk $client
    ) {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function process(Message $message, Context $context)
    {
        $event = unserialize($message->getBody());
        $this->client->sync($event);
    }
}
