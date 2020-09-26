<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Config extends AbstractHelper
{
    const PRIME_CLIENT_HOST = 'prime_data_connect/client_prime/host';
    const PRIME_CLIENT_SOURCE_ID = 'prime_data_connect/client_prime/source_id';
    const PRIME_CLIENT_WRITE_KEY = 'prime_data_connect/client_prime/write_key';
    const PRIME_TRANSPORT_CONFIG = 'prime_data_connect/transport/config';

    protected $data;

    /**
     * Config constructor.
     * @param Context $context
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->data = $data;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getPrimeClientHost(): string
    {
        return (string)$this->scopeConfig->getValue(self::PRIME_CLIENT_HOST);
    }

    /**
     * @return string
     */
    public function getPrimeClientSourceId(): string
    {
        return (string)$this->scopeConfig->getValue(self::PRIME_CLIENT_SOURCE_ID);
    }

    /**
     * @return string
     */
    public function getPrimeClientWriteKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::PRIME_CLIENT_WRITE_KEY);
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return (string)$this->scopeConfig->getValue(self::PRIME_TRANSPORT_CONFIG);
    }

    /**
     * @return string|void
     */
    public function getHost()
    {
        if (!isset($this->data['message_queue'])) {
            return;
        }

        $path = 'prime_data_connect/' . $this->data['message_queue'] . '/host';
        return (string)$this->scopeConfig->getValue($path);
    }

    /**
     * @return string|void
     */
    public function getPort()
    {
        if (!isset($this->data['message_queue'])) {
            return;
        }

        $path = 'prime_data_connect/' . $this->data['message_queue'] . '/port';
        return (string)$this->scopeConfig->getValue($path);
    }

    /**
     * @return bool
     */
    public function getLazyConnect() :bool
    {
        if (!isset($this->data['message_queue'])) {
            return false;
        }

        $path = 'prime_data_connect/' . $this->data['message_queue'] . '/lazy';
        return $this->scopeConfig->isSetFlag($path);
    }
}
