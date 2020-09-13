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

class CustomerRegisterObserver implements ObserverInterface
{
    const EVENT_REGISTER_CUSTOMER = 'Register_Customer';

    /**
     * @var SyncConfig
     */
    private $config;

    /**
     * @var SyncHandle
     */
    private $syncHandle;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerHandle
     */
    private $customerHandle;

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
            /* @var Customer $customer */
            $customerData = $observer->getEvent()->getCustomer();
            try {
                $email = $customerData->getEmail();
                $customer = $this->customerHandle->getCustomerByEmail($email);
                $customerData = $this->customerHandle->processCustomerSync($customer);
                $this->syncHandle->setMessageQueueCode('redis');
                $event[SyncHandle::SESSION_ID] = $this->customerHandle->getCustomerSessionId((int)$customer->getId());
                $this->syncHandle->synDataToPrime(self::EVENT_REGISTER_CUSTOMER, $customerData, $event);
            } catch (\Exception $exception) {
                $this->logger->error(
                    self::EVENT_REGISTER_CUSTOMER,
                    ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
            }
        }
    }
}
