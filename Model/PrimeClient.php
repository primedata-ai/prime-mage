<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use Prime\Client as PrimeClientSdk;
use PrimeData\PrimeDataConnect\Model\MessageQueue\QueueBufferInterface;

class PrimeClient
{
    /**
     * @var PrimeConfig
     */
    protected $primeConfig;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var QueueBufferInterface
     */
    protected $queueBuffer;

    /**
     * @var PrimeClientSdk
     */
    protected $primeClient;

    /**
     * PrimeClient constructor.
     * @param PrimeConfig $primeConfig
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        PrimeConfig $primeConfig,
        array  $data = []
    ) {
        $this->primeConfig = $primeConfig;
        $this->data = $data;
    }

    /**
     * @param QueueBufferInterface $queueBuffer
     * @return $this
     */
    public function setQueueBuffer(QueueBufferInterface $queueBuffer)
    {
        $this->queueBuffer = $queueBuffer;
        return $this;
    }

    /**
     * @return PrimeClientSdk
     */
    public function getPrimeClient()
    {
        if ($this->primeClient) {
            return  $this->primeClient;
        }
        $this->primeClient = new PrimeClientSdk($this->primeConfig->getPrimeConfig(), $this->queueBuffer);
        return $this->primeClient;
    }
}
