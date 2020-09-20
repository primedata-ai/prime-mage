<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Products;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\ProductHandle;
use Psr\Log\LoggerInterface;

class ProductSaveAfterObserver implements ObserverInterface
{
    const EVENT_UPDATE_CREATE_PRODUCT = 'Create_Update_Product';

    /**
     * @var SyncConfig
     */
    private $syncConfig;

    /**
     * @var SyncHandle
     */
    private $syncHandle;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ProductHandle
     */
    private $productHandle;

    /**
     * ProductSaveAfterObserver constructor.
     * @param SyncConfig $config
     * @param SyncHandle $syncHandle
     * @param LoggerInterface $logger
     * @param ProductHandle $productHandle
     */
    public function __construct(
        SyncConfig $config,
        SyncHandle $syncHandle,
        LoggerInterface $logger,
        ProductHandle $productHandle
    )
    {
        $this->syncConfig = $config;
        $this->productHandle = $productHandle;
        $this->syncHandle = $syncHandle;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->syncConfig->isSyncProduct()) {
            /* @var Product $product */
            $product = $observer->getEvent()->getProduct();
            try {
                $productTarget = $this->productHandle->getProductSync($product);
                $sessionId = $this->productHandle->getSessionId();
                // @TODO: Sync Entity Schema later
//                $this->syncHandle->synDataToPrime(self::EVENT_UPDATE_CREATE_PRODUCT, $sessionId, $productTarget);
            } catch (\Exception $exception) {
                $this->logger->error(self::EVENT_UPDATE_CREATE_PRODUCT, [
                    'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()
                ]);
            }
        }
    }
}
