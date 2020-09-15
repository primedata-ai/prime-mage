<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Customer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\CustomerHandle;
use PrimeData\PrimeDataConnect\Model\ProcessData\DeviceHandle;
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
     * @var DeviceHandle
     */
    private $deviceHandle;

    /**
     * CustomerRegisterObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param CustomerHandle $customerHandle
     * @param DeviceHandle $deviceHandle
     * @param SyncHandle $syncHandle
     */
    public function __construct(
        SyncConfig $config,
        LoggerInterface $logger,
        CustomerHandle $customerHandle,
        DeviceHandle $deviceHandle,
        SyncHandle $syncHandle
    ) {
        $this->config = $config;
        $this->syncHandle = $syncHandle;
        $this->logger = $logger;
        $this->customerHandle = $customerHandle;
        $this->deviceHandle = $deviceHandle;
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
                $customerId = $customer->getId();
                $customerDevice = $this->deviceHandle->getDeviceInfo();
                $customerProperty = array_merge($customerDevice, $this->customerHandle->processCustomerSync($customer));
                $this->syncHandle->setMessageQueueCode('redis');
                $this->syncHandle->syncIdentifyToPrime((int)$customerId, $customerProperty);
            } catch (\Exception $exception) {
                $this->logger->error(
                    self::EVENT_REGISTER_CUSTOMER,
                    ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
            }
        }
    }
}
