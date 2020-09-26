<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use PrimeData\PrimeDataConnect\Helper\MessageQueue\MessageConfigInterface;
use Enqueue\AmqpLib\AmqpConnectionFactory;

class RabbitMqAMQPConfig extends Config implements MessageConfigInterface
{
    const RABBIT_PERSISTENT = 'prime_data_connect/rabbitmq_amqp/persistent';
    const RABBIT_USER = 'prime_data_connect/rabbitmq_amqp/user';
    const RABBIT_PASS= 'prime_data_connect/rabbitmq_amqp/pass';

    protected $amqpConnect;
    protected $rabbitMqAMQPParams = [];
    /**
     * @inheritDoc
     */
    public function getMessageQueueConfig()
    {
        if ($this->rabbitMqAMQPParams) {
            return $this->rabbitMqAMQPParams;
        }

        $this->rabbitMqAMQPParams = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'user' => $this->getUser(),
            'pass' => $this->getPassword(),
            'persistent' => $this->getPersistent(),
            'lazy' => $this->getLazyConnect()
        ];

        return  $this->rabbitMqAMQPParams;
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        if ($this->amqpConnect) {
            return $this->amqpConnect;
        }
        $this->amqpConnect = new AmqpConnectionFactory($this->getMessageQueueConfig());

        return $this->amqpConnect;
    }

    /**
     * @return string
     */
    protected function getPersistent()
    {
        return (bool) $this->scopeConfig->getValue(self::RABBIT_PERSISTENT);
    }

    /**
     * @return string
     */
    protected function getUser()
    {
        return (string) $this->scopeConfig->getValue(self::RABBIT_USER);
    }

    /**
     * @return string
     */
    protected function getPassword()
    {
        return (string) $this->scopeConfig->getValue(self::RABBIT_PASS);
    }
}
