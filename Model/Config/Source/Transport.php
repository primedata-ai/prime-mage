<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\Config\Source;

class Transport
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'rabbitmq_amqp', 'label' => __('RabbitMQ AMQP')],
            ['value' => 'rabbitmq_stomp', 'label' => __('RabbitMQ STOMP')],
            ['value' => 'fs', 'label' => __('Filesystem')],
            ['value' => 'sqs', 'label' => __('Amazon AWS SQS')],
            ['value' => 'redis', 'label' => __('Redis')],
            ['value' => 'null', 'label' => __('Null transport')],
        ];
    }
}
