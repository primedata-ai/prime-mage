<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Observer\Customer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;
use PrimeData\PrimeDataConnect\Model\ProcessData\CustomerHandle;
use Psr\Log\LoggerInterface;

class AfterAddressSaveObserver implements ObserverInterface
{
    const EVENT_UPDATE_ADDRESS = 'Update_Customer_Address';

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
     * @var CustomerHandle
     */
    protected $customerHandle;

    /**
     * AfterAddressSaveObserver constructor.
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
        $this->logger = $logger;
        $this->customerHandle = $customerHandle;
        $this->syncHandle = $syncHandle;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSyncCustomer()) {
            /** @var $address Address */
            $address = $observer->getCustomerAddress();
            $customer = $address->getCustomer();
            if ($customer) {
                try {
                    $customerAddress = $this->customerHandle->processCustomerSync($customer);
                    $customerData = $this->customerHandle->getCustomerAddress($customer);
                    $customerInfo = array_merge($customerAddress, $customerData);
                    $this->syncHandle->syncIdentifyToPrime((int)$address->getCustomerId(), $customerInfo);
                } catch (\Exception $exception) {
                    $this->logger->error(
                        self::EVENT_UPDATE_ADDRESS,
                        ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                    );
                }
            }
        }
    }
}
