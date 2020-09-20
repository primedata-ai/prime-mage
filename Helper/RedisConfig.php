<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\ConnectionFactory as QueueConnectionInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\MessageConfigInterface;

class RedisConfig extends Config implements MessageConfigInterface
{
    const REDIS_VENDOR = 'prime_data_connect/redis/vendor';
    const REDIS_PERSISTENT = 'prime_data_connect/redis/persistent';
    const REDIS_DATABASE = 'prime_data_connect/redis/database';
    const REDIS_PASSWORD = 'prime_data_connect/redis/password';
    const REDIS_RETRY_AFTER = 'prime_data_connect/redis/retry_after';
    protected $redisParams = [];

    protected $redisConnect;

    /**
     * @return string
     */
    protected function getVendor()
    {
        return (string) $this->scopeConfig->getValue(self::REDIS_VENDOR);
    }

    /**
     * @return string
     */
    protected function getPersistent()
    {
        return (string) $this->scopeConfig->getValue(self::REDIS_PERSISTENT);
    }

    /**
     * @return string
     */
    protected function getDatabase()
    {
        return (string) $this->scopeConfig->getValue(self::REDIS_DATABASE);
    }

    /**
     * @return string
     */
    protected function getPassword()
    {
        return (string) $this->scopeConfig->getValue(self::REDIS_PASSWORD);
    }

    /**
     * @return string
     */
    protected function getRetryAfter()
    {
        return (string) $this->scopeConfig->getValue(self::REDIS_RETRY_AFTER);
    }

    /**
     * @return array
     */
    public function getMessageQueueConfig()
    {
        if ($this->redisParams) {
            return $this->redisParams;
        }

        $this->redisParams = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'database' => $this->getDatabase(),
            'password' => $this->getPassword(),
            'persistent' => $this->getPersistent(),
            'vendor' => $this->getVendor(),
            'scheme_extensions' => [$this->getVendor()],
            'retry_after' => $this->getRetryAfter(),
            'lazy' => $this->getLazyConnect()
        ];

        return  $this->redisParams;
    }

    /**
     * @return QueueConnectionInterface
     */
    public function getConnection() :QueueConnectionInterface
    {
        if ($this->redisConnect) {
            return $this->redisConnect;
        }
        $this->redisConnect = new RedisConnectionFactory($this->getMessageQueueConfig());

        return $this->redisConnect;
    }
}
