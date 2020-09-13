<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\CustomerHandle;
use Psr\Log\LoggerInterface;

class CustomerUpdateObserver implements ObserverInterface
{
    const EVENT_UPDATE_CUSTOMER = 'Update_Customer';
    /**
     * @var CustomerHandle
     */
    private $customerHandle;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var SyncHandle
     */
    private $syncHandle;
    /**
     * @var SyncConfig
     */
    private $config;

    /**
     * CustomerRegisterObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param CustomerHandle $customerHandle
     * @param SyncHandle $syncHandle
     * @codeCoverageIgnore
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        CustomerHandle $customerHandle,
        SyncHandle $syncHandle
    ) {
        $this->config = $config;
        $this->syncHandle = $syncHandle;
        $this->logger = $logger;
        $this->customerHandle = $customerHandle;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncCustomer()) {
            try {
                /* @var Customer $customer */
                $email = $observer->getEvent()->getEmail();
                $customer = $this->customerHandle->getCustomerByEmail($email);
                $customerData = $this->customerHandle->processCustomerSync($customer);
                $customerAddress = $this->customerHandle->getCustomerAddress($customer);
                $customerData = array_merge($customerData, $customerAddress);
                $this->syncHandle->setMessageQueueCode('redis');
                $event[SyncHandle::SESSION_ID] = $this->customerHandle->getCustomerSessionId((int)$customer->getId());
                $this->syncHandle->synDataToPrime(self::EVENT_UPDATE_CUSTOMER, $customerData, $event);
            } catch (\Exception $exception) {
                $this->logger->error(
                    self::EVENT_UPDATE_CUSTOMER,
                    ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
            }
        }
    }
}
