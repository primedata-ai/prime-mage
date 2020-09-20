<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Products;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
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
     * ReviewProductObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param ReviewHandle $reviewHandle
     * @param SyncHandle $syncHandle
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        ReviewHandle $reviewHandle,
        SyncHandle $syncHandle
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->reviewHandle = $reviewHandle;
        $this->syncHandle = $syncHandle;
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
                $this->syncHandle->synDataToPrime(self::EVENT_SYNC_REVIEW,
                    $profile->getUserID(),
                    $reviewTarget,
                    $properties,
                    $profile->getSessionID());
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
