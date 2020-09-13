<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper\MessageQueue;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use PrimeData\PrimeDataConnect\Helper\RedisConfig;
use PrimeData\PrimeDataConnect\Model\MessageQueue\QueueBuffer;
use PrimeData\PrimeDataConnect\Model\PrimeClient;
use PrimeData\PrimeDataConnect\Model\PrimeConfig;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeEvent;
use Psr\Log\LoggerInterface;

class SyncHandle
{
    const MESSAGE_QUEUE_DEFAULT = 'redis';
    const DEFAULT_SESSION_ID = '1e85YTciGhH6vLfLpmqhJfhFhpq';
    const SESSION_ID = 'session_id';

    /**
     * @var ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var PrimeClient
     */
    private $primeClient;
    /**
     * @var PrimeEvent
     */
    private $primeEvent;
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
     * @var string
     */
    protected $messageQueueCode;

    private $queueConnect;

    /**
     * SyncHandle constructor.
     * @param ObjectManagerFactory $objectManagerFactory
     * @param PrimeClient $primeClient
     * @param PrimeEvent $primeEvent
     * @param PrimeConfig $primeConfig
     * @param QueueBuffer $queueBuffer
     * @param LoggerInterface $logger
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        PrimeClient $primeClient,
        PrimeEvent $primeEvent,
        PrimeConfig $primeConfig,
        QueueBuffer $queueBuffer,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->primeClient = $primeClient;
        $this->primeEvent = $primeEvent;
        $this->primeConfig = $primeConfig;
        $this->queueBuffer  = $queueBuffer;
        $this->logger = $logger;
        $this->data = $data;
        $this->queueConnect = $this->getMessageQueueConnect();
    }

    /**
     * Gets initialized object manager
     *
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
        }
        return $this->objectManager;
    }

    /**
     * @return mixed
     */
    protected function getMessageQueueConnect()
    {
        switch ($this->messageQueueCode) {
            case self::MESSAGE_QUEUE_DEFAULT:
                $this->queueConnect = $this->getObjectManager()->create(RedisConfig::class);
        }

        return $this->queueConnect;
    }

    /**
     * @param string $messageQueueCode
     * @return $this
     */
    public function setMessageQueueCode(string $messageQueueCode)
    {
        $this->messageQueueCode = $messageQueueCode;
        return $this;
    }

    /**
     * @param string $eventName
     * @param array $data
     * @param array $properties
     * @throws \ErrorException
     * @throws \Exception
     */
    public function synDataToPrime(string $eventName, array $data, array $properties = [])
    {
        $this->queueConnect = $this->getMessageQueueConnect();
        if (!$eventName) {
            throw new \ErrorException('Missing Event Name');
        }

        if (!$data) {
            throw new \ErrorException('Missing data to sync');
        }

        $connect = $this->queueConnect->getConnection();
        $queueBuffer = $this->queueBuffer->createQueueManage($connect);
        $primeClient = $this->primeClient->setQueueBuffer($queueBuffer)->getPrimeClient();

        $this->primeEvent = $this->getEventData($properties);
        $primeClient->track($eventName, $data, $this->primeClient);
    }

    /**
     * @param array $properties
     * @return array|PrimeEvent
     */
    public function getEventData(array $properties)
    {
        if (!$properties) {
            $this->primeEvent->setSessionId(self::DEFAULT_SESSION_ID);
        }

        if (isset($properties[self::SESSION_ID])) {
            $this->primeEvent->setSessionId($properties[self::SESSION_ID]);
        }

        return  $this->primeEvent;
    }
}
