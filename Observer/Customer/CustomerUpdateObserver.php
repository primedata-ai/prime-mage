<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Customer;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\CustomerHandle;
use PrimeData\PrimeDataConnect\Model\ProcessData\DeviceHandle;
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
     * @var
     */
    protected $deviceHandle;

    /**
     * CustomerRegisterObserver constructor.
     * @param SyncConfig $config
     * @param LoggerInterface $logger
     * @param CustomerHandle $customerHandle
     * @param DeviceHandle $deviceHandle
     * @param SyncHandle $syncHandle
     * @codeCoverageIgnore
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
            $email = $observer->getEvent()->getEmail();
            try {
                $customer = $this->customerHandle->getCustomerByEmail($email);
                $customerData = $this->customerHandle->processCustomerSync($customer);
                $customerAddress = $this->customerHandle->getCustomerAddress($customer);
                $customerDevice = $this->deviceHandle->getDeviceInfo();
                $customerData = array_merge($customerData, $customerAddress, $customerDevice);
                $this->syncHandle->setMessageQueueCode('redis');
                $this->syncHandle->syncIdentifyToPrime((int)$customer->getId(), $customerData);
            } catch (Exception $exception) {
                $this->logger->error(
                    self::EVENT_UPDATE_CUSTOMER,
                    ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
            }
        }
    }
}
