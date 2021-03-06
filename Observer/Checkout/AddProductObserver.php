<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
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
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * AddProductObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param CheckoutCartHandle $checkoutCartHandle
     * @param SyncHandle $syncHandle
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        CheckoutCartHandle $checkoutCartHandle,
        SyncHandle $syncHandle,
        CookieManagerInterface $cookieManager
) {
        $this->config = $config;
        $this->logger = $logger;
        $this->checkoutCartHandle = $checkoutCartHandle;
        $this->syncHandle = $syncHandle;
        $this->cookieManger = $cookieManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncCartItem()) {
            try {
                $cartItem = $observer->getEvent()->getQuoteItem();
                $cookieXSessionId = $this->cookieManger->getCookie('XSessionId');
                $sessionId = ($cookieXSessionId)? $cookieXSessionId : $this->checkoutCartHandle->getSessionId();
                $item = $this->checkoutCartHandle->getCartItemData($cartItem);
                $properties = $this->checkoutCartHandle->getCartProperties($cartItem);
                $this->syncHandle->synDataToPrime(self::SYNC_EVENT, $sessionId, $item, $properties);
            } catch (\Exception $e) {
                $this->logger->error(self::SYNC_EVENT, [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString()
                ]);
            }
        }
    }
}
