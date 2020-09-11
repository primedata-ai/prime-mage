<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Redis\RedisContext;
use Interop\Queue\Exception;
use Interop\Queue\InvalidDestinationException;
use Interop\Queue\InvalidMessageException;
use Magento\Framework\Serialize\SerializerInterface;
use PrimeData\PrimeDataConnect\Api\ProducerInterface;
use PrimeData\PrimeDataConnect\Helper\RedisConfig;

class Producer implements ProducerInterface
{
    protected $serializer;
    /**
     * @var RedisConnectionFactory
     */
    private $redisConnectionFactory;
    /**
     * @var RedisConfig
     */
    private $config;

    /**
     * @var RedisContext
     */
    private $context;

    public function __construct(
        RedisConfig $config,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->redisConnectionFactory = new RedisConnectionFactory($this->config->getRedisConfig());
        $this->context = $this->redisConnectionFactory->createContext();
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(string $topic, object $msg)
    {
        $queueName = 'enqueue.app.default';
        $this->context->createQueue($queueName);
        $topic= $this->context->createTopic($topic);
        $message = $this->context->createMessage($this->serializer->serialize($msg));

        try {
            $this->context->createProducer()->send($topic, $message);
        } catch (InvalidDestinationException $e) {
            print_r($e->getMessage());
        } catch (InvalidMessageException $e) {
            print_r($e->getMessage());
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }
}
