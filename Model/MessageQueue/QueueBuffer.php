<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\MessageQueue;

use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Exception;
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
        $this->producer = $this->queueContext->createProducer();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(string $topic, \JsonSerializable $msg)
    {
        $this->queueContext->createQueue(self::QUEUE_NAME_DEFAULT);
        $topic = $this->queueContext->createTopic($topic);
        $message = $this->queueContext->createMessage($this->serializer->serialize($msg));

        try {
            $this->producer->send($topic, $message);
        } catch (Exception\InvalidDestinationException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception\InvalidMessageException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
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
