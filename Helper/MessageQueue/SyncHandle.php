<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper\MessageQueue;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Tracking\Event;
use Prime\Tracking\Source;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Helper\RedisConfig;
use PrimeData\PrimeDataConnect\Model\MessageQueue\QueueBuffer;
use PrimeData\PrimeDataConnect\Model\PrimeClient;
use PrimeData\PrimeDataConnect\Model\PrimeConfig;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeEvent;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeSource;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;
use Psr\Log\LoggerInterface;

class SyncHandle
{
    const MESSAGE_QUEUE_DEFAULT = 'redis';
    const SCOPE_DEFAULT = 'website';
    const SOURCE_DEFINE = 'site';

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

    /**
     * @var mixed
     */
    private $queueConnect;
    /**
     * @var PrimeSource
     */
    protected $primeSource;
    /**
     * @var PrimeTarget
     */
    protected $primeTarget;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SyncHandle constructor.
     * @param ObjectManagerFactory $objectManagerFactory
     * @param PrimeClient $primeClient
     * @param PrimeEvent $primeEvent
     * @param PrimeSource $primeSource
     * @param PrimeConfig $primeConfig
     * @param QueueBuffer $queueBuffer
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        PrimeClient $primeClient,
        PrimeEvent $primeEvent,
        PrimeSource $primeSource,
        PrimeConfig $primeConfig,
        QueueBuffer $queueBuffer,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->primeClient = $primeClient;
        $this->primeEvent = $primeEvent;
        $this->primeSource = $primeSource;
        $this->primeConfig = $primeConfig;
        $this->queueBuffer  = $queueBuffer;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
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
     * @param string $sessionId
     * @param Target $target
     * @throws \ErrorException
     * @throws NoSuchEntityException
     */
    public function synDataToPrime(string $eventName, string $sessionId, Target $target)
    {
        $this->queueConnect = $this->getMessageQueueConnect();
        if (!$eventName) {
            throw new \ErrorException('Missing Event Name');
        }

        if (!$sessionId) {
            throw new \ErrorException('Missing Session Id event to sync');
        }

        $connect = $this->queueConnect->getConnection();
        $queueBuffer = $this->queueBuffer->createQueueManage($connect);
        $primeClient = $this->primeClient->setQueueBuffer($queueBuffer)->getPrimeClient();
        $source = $this->createPrimeSource();

        $primeClient->track(
            $eventName,
            [],
            Event::withSessionID($sessionId),
            Event::withSource($source),
            Event::withTarget($target)
        );
    }

    /**
     * @param int $customerId
     * @param array $data
     * @throws \ErrorException
     */
    public function syncIdentifyToPrime(int $customerId, array $data)
    {
        $this->queueConnect = $this->getMessageQueueConnect();
        if (!$customerId) {
            throw new \ErrorException('Missing Customer Id');
        }

        if (!$data) {
            throw new \ErrorException('Missing data to sync');
        }

        $connect = $this->queueConnect->getConnection();
        $queueBuffer = $this->queueBuffer->createQueueManage($connect);
        $primeClient = $this->primeClient->setQueueBuffer($queueBuffer)->getPrimeClient();

        $primeClient->identify($customerId, $data);
    }

    /**
     * @return Source
     * @throws NoSuchEntityException
     */
    protected function createPrimeSource()
    {
        $this->primeSource->setItemType(self::SOURCE_DEFINE);
        $storeUrl = $this->storeManager->getStore()->getBaseUrl();
        $this->primeSource->setItemId($storeUrl);

        return $this->primeSource->createPrimeSource();
    }
}
