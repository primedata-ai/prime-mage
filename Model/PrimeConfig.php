<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use Prime\PrimeConfig as PrimeConfigSdk;
use PrimeData\PrimeDataConnect\Helper\Config;

class PrimeConfig
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var PrimeConfigSdk
     */
    private $primeConfig;

    /**
     * PrimeConfig constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
        $this->primeConfig = new PrimeConfigSdk(
            $this->config->getPrimeClientSourceId(),
            $this->config->getPrimeClientWriteKey()
        );
    }

    /**
     * @return PrimeConfigSdk
     */
    public function getPrimeConfig()
    {
        return $this->primeConfig;
    }
}
