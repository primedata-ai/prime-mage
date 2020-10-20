<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Plugin\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\SalesOrderHandle;
use Psr\Log\LoggerInterface;

class OrderRepositoryPlugin
{
    const EVENT_SYNC = 'create_sale_order';
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
     * @var SalesOrderHandle
     */
    protected $saleOrderHandle;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * OrderRepositoryPlugin constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param SalesOrderHandle $saleOrderHandle
     * @param SyncHandle $syncHandle
     * @param CookieManagerInterface $cookieManager
     * @codeCoverageIgnore
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        SalesOrderHandle $saleOrderHandle,
        SyncHandle $syncHandle,
        CookieManagerInterface $cookieManager
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->saleOrderHandle = $saleOrderHandle;
        $this->syncHandle = $syncHandle;
        $this->cookieManger = $cookieManager;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $entity
     * @return OrderInterface
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $result, OrderInterface $entity)
    {
        if (!$this->config->isSyncOrder()) {
            return $result;
        }

        $this->syncOrder($result);
        return $result;
    }

    /**
     * @param OrderInterface $order
     */
    protected function syncOrder(OrderInterface $order)
    {
        try {
            $targetOrder = $this->saleOrderHandle->processOrderData($order);
            $properties = $this->saleOrderHandle->getOrderProperties($order);
            $profile = $this->saleOrderHandle->getProfile($order);
            $cookieXSessionId = $this->cookieManger->getCookie('XSessionId');
            $sessionId = ($cookieXSessionId)? $cookieXSessionId : $profile->getSessionID();
            $this->syncHandle->synDataToPrime(
                self::EVENT_SYNC,
                (string)$profile->getUserID(),
                $targetOrder,
                $properties,
                $sessionId
            );
        } catch (\Exception $e) {
            $this->logger->error(self::EVENT_SYNC, [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }
}
