<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\MessageQueue;

use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Producer;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class QueueBuffer implements QueueBufferInterface
{
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
    public function sendMessage(string $topic, object $msg)
    {
        $queueName = 'enqueue.app.default';
        $this->queueContext->createQueue($queueName);
        $topic = $this->queueContext->createTopic($topic);
        $message = $this->queueContext->createMessage($this->serializer->serialize($msg));

        try {
            $this->producer->send($topic, $message);
        } catch (Exception\InvalidDestinationException $e) {
            print_r($e->getMessage());
        } catch (Exception\InvalidMessageException $e) {
            print_r($e->getMessage());
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }
}
