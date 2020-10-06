<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper\MessageQueue;

use ErrorException;
use Interop\Queue\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Client;
use Prime\Tracking\Event;
use Prime\Tracking\Source;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Helper\Config as PrimeHelperConfig;
use PrimeData\PrimeDataConnect\Helper\RabbitMqAMQPConfig;
use PrimeData\PrimeDataConnect\Helper\RedisConfig;
use PrimeData\PrimeDataConnect\Model\MessageQueue\QueueBuffer;
use PrimeData\PrimeDataConnect\Model\PrimeClient;
use PrimeData\PrimeDataConnect\Model\PrimeConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\DeviceHandle;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeSource;
use Psr\Log\LoggerInterface;

class SyncHandle
{
    const MESSAGE_QUEUE_DEFAULT = 'redis';
    const SCOPE_DEFAULT = 'website';
    const SOURCE_DEFINE = 'site';
    const MESSAGE_QUEUE_RABBIT_AMQP = 'rabbitmq_amqp';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var PrimeClient
     */
    private $primeClient;
    /**
     * @var PrimeConfig
     */
    private $primeConfig;
    /**
     * @var QueueBuffer
     */
    private $queueBuffer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var mixed
     */
    private $queueConnect;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var QueueBuffer
     */
    protected $queueManage;

    /**
     * @var DeviceHandle
     */
    private $deviceHandler;
    /**
     * @var Client
     */
    private $client;

    /**
     * SyncHandle constructor.
     * @param ObjectManagerInterface $objectManager
     * @param PrimeClient $primeClient
     * @param PrimeConfig $primeConfig
     * @param QueueBuffer $queueBuffer
     * @param StoreManagerInterface $storeManager
     * @param PrimeHelperConfig $helperConfig
     * @param DeviceHandle $deviceHandle
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PrimeClient $primeClient,
        PrimeConfig $primeConfig,
        QueueBuffer $queueBuffer,
        StoreManagerInterface $storeManager,
        PrimeHelperConfig $helperConfig,
        DeviceHandle $deviceHandle,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->primeClient = $primeClient;
        $this->deviceHandler = $deviceHandle;
        $this->primeConfig = $primeConfig;
        $this->queueBuffer = $queueBuffer;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        $messageConfig = $helperConfig->getTransport();
        $this->queueConnect = $this->getMessageQueueConnect($messageConfig);
        $connect = $this->queueConnect->getConnection();
        $this->queueManage = $this->queueBuffer->createQueueManage($connect);
        $this->client = $this->primeClient->setQueueBuffer($this->queueManage)->getPrimeClient();
    }

    /**
     * @return Client
     */
    public function getPrimeClient()
    {
        return $this->client;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->queueManage->getContext();
    }

    /**
     * @param string $transport
     * @return MessageConfigInterface
     */
    protected function getMessageQueueConnect(string $transport)
    {
        switch ($transport) {
            case self::MESSAGE_QUEUE_DEFAULT:
                $this->queueConnect = $this->objectManager->create(RedisConfig::class);
                break;
            case self::MESSAGE_QUEUE_RABBIT_AMQP:
                $this->queueConnect = $this->objectManager->create(RabbitMqAMQPConfig::class);
                break;
            default:
                $this->queueConnect = $this->objectManager->create(RedisConfig::class);
        }

        return $this->queueConnect;
    }

    /**
     * @param string $eventName
     * @param string $userId
     * @param Target $target
     * @param array $properties
     * @param string $sessionId
     * @throws ErrorException
     * @throws NoSuchEntityException
     */
    public function synDataToPrime(
        string $eventName,
        string $userId,
        Target $target,
        array $properties = [],
        string $sessionId = ""
    ) {
        if (!$eventName) {
            throw new ErrorException('Missing Event Name');
        }

        if (!$userId) {
            throw new ErrorException('Missing UserId event to sync');
        }

        $source = $this->createPrimeSource();
        $primeClient = $this->getPrimeClient();
        $primeClient->track(
            $eventName,
            $properties,
            Event::withProfileID($userId),
            Event::withSessionID($sessionId),
            Event::withSource($source),
            Event::withTarget($target)
        );
    }

    /**
     * @param int $customerId
     * @param array $data
     * @throws ErrorException
     */
    public function syncIdentifyToPrime(int $customerId, array $data)
    {
        if (!$customerId) {
            throw new ErrorException('Missing Customer Id');
        }

        if (!$data) {
            throw new ErrorException('Missing data to sync');
        }

        $primeClient = $this->getPrimeClient();
        $primeClient->identify($customerId, $data);
    }

    /**
     * @return Source
     * @throws NoSuchEntityException
     */
    protected function createPrimeSource()
    {
        $storeUrl = $this->storeManager->getStore()->getBaseUrl();
        $primeSource = new PrimeSource();
        $primeSource->setItemType(self::SOURCE_DEFINE);
        $primeSource->setItemId($storeUrl);
        $primeSource->setProperties($this->deviceHandler->getDeviceInfo());

        return $primeSource->createPrimeSource();
    }
}
