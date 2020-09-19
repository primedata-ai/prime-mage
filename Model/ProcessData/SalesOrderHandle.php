<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;

class SalesOrderHandle
{
    const TYPE_TARGET = 'create_order';

    /**
     * @var CustomerHandle
     */
    protected $customerHandle;
    /**
     * @var DeviceHandle
     */
    protected $deviceHandle;
    /**
     * @var PrimeTarget
     */
    protected $target;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * SalesOrderHandle constructor.
     * @param CustomerHandle $customerHandle
     * @param DeviceHandle $deviceHandle
     * @param Session $checkoutSession
     * @param PrimeTarget $target
     */
    public function __construct(
        CustomerHandle $customerHandle,
        DeviceHandle $deviceHandle,
        Session $checkoutSession,
        PrimeTarget $target
    ) {
        $this->customerHandle = $customerHandle;
        $this->deviceHandle = $deviceHandle;
        $this->checkoutSession = $checkoutSession;
        $this->target = $target;
    }

    /**
     * @param OrderInterface $order
     * @return Target
     */
    public function processOrderData(OrderInterface $order)
    {
        $this->target->setItemType(self::TYPE_TARGET);
        $this->target->setItemId((string)$order->getEntityId());
        $orderProperties = $this->getOrderProperties($order);
        $customerProperties = $this->getCustomerProperties($order);
        $deviceProperties = $this->deviceHandle->getDeviceInfo();

        $properties = array_merge($orderProperties, $customerProperties, $deviceProperties);
        $this->target->setProperties($properties);
        return $this->target->createPrimeTarget();
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->checkoutSession->getSessionId();
    }

    protected function getOrderProperties(OrderInterface $order) :array
    {
        return [
            'increment_id' => $order->getIncrementId(),
            'subtotal'  => $order->getBaseSubtotal(),
            'grand_total' => $order->getGrandTotal(),
            'discount_amount' => $order->getDiscountAmount(),
            'tax_amount' => $order->getTaxAmount(),
            'currency' => $order->getOrderCurrencyCode()
        ];
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    protected function getCustomerProperties(OrderInterface $order) :array
    {
        return [
            'email' => $order->getCustomerEmail(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'customer_id' => $order->getCustomerId()
        ];
    }
}
