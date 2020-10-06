<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\MessageQueue;

use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class QueueBuffer implements QueueBufferInterface
{
    const QUEUE_NAME_DEFAULT = 'primedata-events';
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Context
     */
    private $queueContext;

    /**
     * @var Producer
     */
    private $producer;

    private $consumer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * QueueBuffer constructor.
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param ConnectionFactory $queueConnection
     * @return $this
     */
    public function createQueueManage(ConnectionFactory $queueConnection)
    {
        $this->queueContext = $queueConnection->createContext();
        return $this;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->queueContext;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function sendMessage(string $topic, \JsonSerializable $msg)
    {
        $queue = $this->queueContext->createQueue(self::QUEUE_NAME_DEFAULT);
        $this->queueContext->createTopic($topic);
        $message = $this->queueContext->createMessage(serialize($msg));

        try {
            $this->queueContext->createProducer()->send($queue, $message);
        } catch (InvalidDestinationException $e) {
            throw new \Exception($e->getMessage());
        } catch (InvalidMessageException $e) {
            throw new \Exception($e->getMessage());
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return Message|null
     */
    public function getMessage()
    {
        $queue = $this->queueContext->createQueue(self::QUEUE_NAME_DEFAULT);
        $this->consumer = $this->queueContext->createConsumer($queue);
        $message = $this->consumer->receive();
        $this->consumer->acknowledge($message);

        return $message;
    }
}
