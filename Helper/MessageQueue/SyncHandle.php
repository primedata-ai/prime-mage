<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper\MessageQueue;

use ErrorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Tracking\Event;
use Prime\Tracking\Source;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Helper\Config as PrimeHelperConfig;
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
    const LOG_PREFIX = 'Prime_SyncHandle';

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
     * @var SerializerInterface
     */
    private $serializer;

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
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->primeClient = $primeClient;
        $this->deviceHandler = $deviceHandle;
        $this->primeConfig = $primeConfig;
        $this->queueBuffer = $queueBuffer;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->logger = $logger;

        $messageConfig = $helperConfig->getTransport();
        $this->queueConnect = $this->getMessageQueueConnect($messageConfig);
        $connect = $this->queueConnect->getConnection();
        $this->queueManage = $this->queueBuffer->createQueueManage($connect);
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

        $primeClient = $this->primeClient->setQueueBuffer($this->queueManage)->getPrimeClient();
        $source = $this->createPrimeSource();
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

        $primeClient = $this->primeClient->setQueueBuffer($this->queueManage)->getPrimeClient();
        $primeClient->identify($customerId, $data);
    }

    public function sendDataToPrime()
    {
        $primeClient = $this->primeClient->setQueueBuffer($this->queueManage)->getPrimeClient();
        $message = $this->queueManage->getMessage();
        $body = $message->getBody();
        $eventData = $this->serializer->unserialize($body);
        try {
            $event = $this->convertToEvent($eventData);
            // Because the sync using Event Object so we need convert the array to event Object
            $primeClient->sync($event);
        } catch (ErrorException $e) {
            $this->logger->error(self::LOG_PREFIX, [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @param array $eventData
     * @return Event
     * @throws ErrorException
     */
    protected function convertToEvent(array $eventData)
    {
        if (!$eventData['events'][0]) {
            throw new ErrorException('Not have Prime Event Data');
        }

        $event =$eventData['events'][0];

        if (!$event['eventType']) {
            throw new ErrorException('Missing Prime eventType');
        }

        if (!$event['scope']) {
            throw new ErrorException('Missing Prime scope');
        }

        return new Event(
            $event['eventType'],
            $event['scope'],
            $event
        );
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
