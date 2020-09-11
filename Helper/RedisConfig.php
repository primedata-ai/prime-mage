<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use PrimeData\PrimeDataConnect\Helper\Config;

class RedisConfig extends Config
{
    const REDIS_VENDOR = 'prime_data_connect/redis/vendor';
    const REDIS_PERSISTENT = 'prime_data_connect/redis/persistent';
    const REDIS_DATABASE = 'prime_data_connect/redis/database';
    const REDIS_PASSWORD = 'prime_data_connect/redis/password';
    const REDIS_RETRY_AFTER = 'prime_data_connect/redis/retry_after';

    protected $redisParams = [];

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
    public function getRedisConfig()
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

}
