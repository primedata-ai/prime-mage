<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use Prime\Client as PrimeClientSdk;
use PrimeData\PrimeDataConnect\Api\ProducerInterface;

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
     * @var ProducerInterface
     */

    /**
     * @var ProducerInterface
     */
    protected $producer;

    /**
     * @var PrimeClientSdk
     */
    protected $primeClient;

    /**
     * PrimeClient constructor.
     * @param PrimeConfig $primeConfig
     * @param ProducerInterface $producer
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        PrimeConfig $primeConfig,
        ProducerInterface $producer,
        array  $data = []
    ) {
        $this->primeConfig = $primeConfig;
        $this->data = $data;
        $this->producer = $producer;
        $this->primeClient = new PrimeClientSdk($this->primeConfig->getPrimeConfig(), $this->producer);
    }

    /**
     * @return PrimeClientSdk
     */
    public function getPrimeClient()
    {
        return $this->primeClient;
    }
}
