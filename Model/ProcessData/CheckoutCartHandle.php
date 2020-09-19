<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;

class CheckoutCartHandle
{
    const TYPE_TARGET = 'add_item_to_cart';

    protected $cartItemRepository;
    /**
     * @var DeviceHandle
     */
    protected $deviceHandle;
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var PrimeTarget
     */
    protected $target;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutCartHandle constructor.
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param DeviceHandle $deviceHandle
     * @param Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        DeviceHandle $deviceHandle,
        PrimeTarget $target,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->deviceHandle = $deviceHandle;
        $this->target = $target;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return Target
     * @throws NoSuchEntityException
     */
    public function processCartItemData(CartItemInterface $cartItem)
    {
        $this->target->setItemType(self::TYPE_TARGET);
        $this->target->setItemId((string)$cartItem->getItemId());

        $cartProperties = $this->getProperties($cartItem);
        $deviceProperties = $this->deviceHandle->getDeviceInfo();
        $properties = array_merge($cartProperties, $deviceProperties);
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

    /**
     * @param CartItemInterface $cartItem
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProperties(CartItemInterface $cartItem)
    {
        return [
            'cart_id'   => $this->checkoutSession->getQuoteId(),
            'product_sku' => $cartItem->getSku(),
            'qty'         => $cartItem->getQty(),
            'price'       => $cartItem->getPrice(),
            'discount_amount' => $cartItem->getDiscountAmount(),
            'total_value'     => $cartItem->getRowTotal(),
            'currency'      => $this->storeManager->getStore()->getBaseCurrencyCode()
        ];
    }
}
