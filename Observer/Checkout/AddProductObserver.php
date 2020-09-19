<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\CheckoutCartHandle;
use Psr\Log\LoggerInterface;

class AddProductObserver implements ObserverInterface
{
    const SYNC_EVENT = 'ADD_PRODUCT_TO_CART';
    /**
     * @var SyncConfig
     */
    protected $config;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SyncHandle
     */
    protected $syncHandle;
    /**
     * @var CheckoutCartHandle
     */
    private $checkoutCartHandle;

    /**
     * AddProductObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param CheckoutCartHandle $checkoutCartHandle
     * @param SyncHandle $syncHandle
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        CheckoutCartHandle $checkoutCartHandle,
        SyncHandle $syncHandle
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->checkoutCartHandle = $checkoutCartHandle;
        $this->syncHandle = $syncHandle;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncCartItem()) {
            try {
                $cartItem = $observer->getEvent()->getQuoteItem();
                $sessionId = $this->checkoutCartHandle->getSessionId();
                $properties = $this->checkoutCartHandle->processCartItemData($cartItem);
                $this->syncHandle->setMessageQueueCode('redis');
                $this->syncHandle->synDataToPrime(self::SYNC_EVENT, $sessionId, $properties);
            } catch (\Exception $e) {
                $this->logger->error(self::SYNC_EVENT, [
                   'message' => $e->getMessage(),
                   'trace'    => $e->getTraceAsString()
                ]);
            }
        }
    }
}
