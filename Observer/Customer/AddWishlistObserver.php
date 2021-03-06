<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\WishlistHandle;
use Psr\Log\LoggerInterface;

class AddWishlistObserver implements ObserverInterface
{
    const EVENT_WISHLIST = 'Add_Wishlist';
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
     * @var WishlistHandle
     */
    protected $wishlistHandle;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * AddWishlistObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param WishlistHandle $wishlistHandle
     * @param SyncHandle $syncHandle
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        WishlistHandle $wishlistHandle,
        SyncHandle $syncHandle,
        CookieManagerInterface $cookieManager
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->wishlistHandle = $wishlistHandle;
        $this->syncHandle = $syncHandle;
        $this->cookieManger = $cookieManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncCustomer()) {
            $wishlist = $observer->getEvent()->getWishlist();
            $item = $observer->getEvent()->getItem();
            try {
                $wishlistTarget = $this->wishlistHandle->processWishlistData($wishlist, $item);
                $userInfo = $this->wishlistHandle->getProfile($wishlist);
                $cookieXSessionId = $this->cookieManger->getCookie('XSessionId');
                $sessionId = ($cookieXSessionId)? $cookieXSessionId : $userInfo->getSessionID();
                $this->syncHandle->synDataToPrime(
                    self::EVENT_WISHLIST,
                    $userInfo->getUserID(),
                    $wishlistTarget,
                    [],
                    $sessionId
                );
            } catch (\Exception $exception) {
                $this->logger->error(self::EVENT_WISHLIST, [
                    'message' => $exception->getMessage(),
                    'trace'   => $exception->getTraceAsString()
                ]);
            }
        }
    }
}
