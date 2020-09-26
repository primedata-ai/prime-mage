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
            ['value' => 'redis', 'label' => __('Redis')]
        ];
    }
}
