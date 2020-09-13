<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Products;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\ProductHandle;
use Psr\Log\LoggerInterface;

class ProductDeleteBeforeObserver implements ObserverInterface
{
    const EVENT_DELETE_PRODUCT = 'Delete_Product';

    /**
     * @var SyncConfig
     */
    private $syncConfig;
    /**
     * @var ProductHandle
     */
    private $productHandle;
    /**
     * @var SyncHandle
     */
    private $syncHandle;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SyncConfig $config,
        SyncHandle $syncHandle,
        LoggerInterface $logger,
        ProductHandle $productHandle
    ) {
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
            try {
                $product = $observer->getEvent()->getProduct();
                $productSyncData = $this->productHandle->processProductSync($product);
                $this->syncHandle->setMessageQueueCode('redis');
                $this->syncHandle->synDataToPrime(self::EVENT_DELETE_PRODUCT, $productSyncData);
            } catch (\Exception $exception) {
                $this->logger->error(self::EVENT_DELETE_PRODUCT, [
                  'error' =>  $exception->getMessage(), 'trace' => $exception->getTraceAsString()
                ]);
            }
        }
    }
}
