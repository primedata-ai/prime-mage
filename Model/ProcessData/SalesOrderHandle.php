<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;
use PrimeData\PrimeDataConnect\Model\UserInfo;

class SalesOrderHandle
{
    const TYPE_TARGET = 'order';

    /**
     * @var CustomerHandle
     */
    protected $customerHandle;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * SalesOrderHandle constructor.
     * @param CustomerHandle $customerHandle
     * @param Session $checkoutSession
     */
    public function __construct(
        CustomerHandle $customerHandle, Session $checkoutSession
    )
    {
        $this->customerHandle = $customerHandle;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param OrderInterface $order
     * @return Target
     */
    public function processOrderData(OrderInterface $order)
    {
        $target = new PrimeTarget();
        $target->setItemType(self::TYPE_TARGET);
        $target->setItemId((string)$order->getEntityId());
        $orderProperties = $this->getOrderInfo($order);
        $target->setProperties($orderProperties);
        return $target->createPrimeTarget();
    }

    /**
     * @param OrderInterface $order
     * @return UserInfo
     */
    public function getProfile(OrderInterface $order)
    {
        return new UserInfo($order->getCustomerId(), $this->getSessionId());
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->checkoutSession->getSessionId();
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderInfo(OrderInterface $order)
    {
        return [
            'weight'          => $order->getWeight(),
            'virtual'         => $order->getIsVirtual(),
            'coupon'          => !empty($order->getCouponCode()),
            'tax_amount'      => $order->getTaxAmount(),
            'increment_id'    => $order->getIncrementId(),
            'subtotal'        => $order->getBaseSubtotal(),
            'grand_total'     => $order->getGrandTotal(),
            'discount_amount' => $order->getDiscountAmount(),
        ];
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getOrderProperties(OrderInterface $order): array
    {
        return [
            'discount_value' => $order->getTotalPaid() - $order->getDiscountAcmount(),
            'total_value'    => $order->getTotalPaid(),
            'currency'       => $order->getOrderCurrencyCode()
        ];
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function getCustomerProperties(OrderInterface $order): array
    {
        return [
            'email'       => $order->getCustomerEmail(),
            'first_name'  => $order->getCustomerFirstname(),
            'last_name'   => $order->getCustomerLastname(),
            'customer_id' => $order->getCustomerId()
        ];
    }
}
