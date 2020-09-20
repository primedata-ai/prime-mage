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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutCartHandle constructor.
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    )
    {
        $this->cartItemRepository = $cartItemRepository;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return Target
     * @throws NoSuchEntityException
     */
    public function getCartItemData(CartItemInterface $cartItem)
    {
        $target = new PrimeTarget();
        $target->setItemType(self::TYPE_TARGET);
        $target->setItemId((string)$cartItem->getItemId());
        $target->setProperties($this->getProperties($cartItem));

        return $target->createPrimeTarget();
    }

    /**
     * @param CartItemInterface $cartItem
     * @return array
     */
    public function getCartProperties(CartItemInterface $cartItem)
    {
        return [
            'total_value'    => $cartItem->getRowTotal(),
            'discount_value' => $cartItem->getRowTotal() - (float)$cartItem->getDiscountAmount(),
            'currency'       => $this->storeManager->getStore()->getBaseCurrencyCode()
        ];
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
            'cart_id'         => $this->checkoutSession->getQuoteId(),
            'product_sku'     => $cartItem->getSku(),
            'qty'             => $cartItem->getQty(),
            'price'           => $cartItem->getPrice(),
            'discount_amount' => $cartItem->getDiscountAmount(),
        ];
    }
}
