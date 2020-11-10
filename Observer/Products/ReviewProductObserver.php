<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Products;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\ReviewHandle;
use Psr\Log\LoggerInterface;

class ReviewProductObserver implements ObserverInterface
{
    const EVENT_SYNC_REVIEW = 'REVIEW_PRODUCT';

    /**
     * @var SyncConfig
     */
    protected $config;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ReviewHandle
     */
    protected $reviewHandle;
    /**
     * @var SyncHandle
     */
    protected $syncHandle;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * ReviewProductObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param ReviewHandle $reviewHandle
     * @param SyncHandle $syncHandle
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        ReviewHandle $reviewHandle,
        SyncHandle $syncHandle,
        CookieManagerInterface $cookieManager
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->reviewHandle = $reviewHandle;
        $this->syncHandle = $syncHandle;
        $this->cookieManger = $cookieManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncReview()) {
            $review = $observer->getEvent()->getDataObject();
            try {
                $reviewTarget = $this->reviewHandle->getProductInfo($review);
                $properties = $this->reviewHandle->getReviewInfo($review);
                $profile = $this->reviewHandle->getProfile($review);
                $cookieXSessionId = $this->cookieManger->getCookie('XSessionId');
                $sessionId = ($cookieXSessionId)? $cookieXSessionId : $profile->getSessionID();
                $this->syncHandle->synDataToPrime(
                    self::EVENT_SYNC_REVIEW,
                    $profile->getUserID(),
                    $reviewTarget,
                    $properties,
                    $sessionId
                );
            } catch (\Exception $e) {
                $this->logger->error(
                    self::EVENT_SYNC_REVIEW,
                    [
                        'message' => $e->getMessage(),
                        'trace'   => $e->getTraceAsString()
                    ]
                );
            }
        }
    }
}
