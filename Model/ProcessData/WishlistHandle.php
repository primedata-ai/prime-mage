<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;
use Prime\Tracking\Target;

class WishlistHandle
{
    const TYPE_TARGET = 'wishlist';

    protected $customerRepository;

    /**
     * @var ProductHandle
     */
    protected $productHandle;

    /**
     * @var CustomerHandle
     */
    protected $customerHandle;
    /**
     * @var PrimeTarget
     */
    protected $target;

    /**
     * WishlistHandle constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerHandle $customerHandle
     * @param PrimeTarget $target
     * @param ProductHandle $productHandle
     * @codeCoverageIgnore
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerHandle $customerHandle,
        PrimeTarget $target,
        ProductHandle $productHandle
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerHandle = $customerHandle;
        $this->target = $target;
        $this->productHandle = $productHandle;
    }

    /**
     * @param Wishlist $wishlist
     * @param Item $item
     * @return Target
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processWishlistData(Wishlist $wishlist, Item $item)
    {
        $wishlistId = $wishlist->getId();
        $this->target->setItemType(self::TYPE_TARGET);
        $this->target->setItemId($wishlistId);
        $wishlistName = $wishlist->getName();

        $wishlistData['wishlist_name'] = $wishlistName;
        $customerData = $this->getCustomerData($wishlist);
        $itemData = $this->getWishlistItem($item);

        $properties = array_merge($wishlistData, $customerData, $itemData);
        $this->target->setProperties($properties);

        return $this->target->createPrimeTarget();
    }

    /**
     * @param Wishlist $wishlist
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getCustomerData(Wishlist $wishlist)
    {
        $customerId = $wishlist->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);
        $customerEmail = $customer->getEmail();

        return [
            'email' => $customerEmail,
            'customer_id' => $customerId,
            'customer_session' => $this->customerHandle->getCustomerSessionId((int)$customerId)
        ];
    }

    /**
     * @param Item $item
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getWishlistItem(Item $item)
    {
        $product = $item->getProduct();
        $productData = $this->productHandle->getPriceDataProduct($product);
        $itemData['item_id'] = $item->getId();
        $itemData['item_qty'] = $product->getQty();
        return array_merge($itemData, $productData);
    }

    /**
     * @param Wishlist $wishlist
     * @return string
     * @throws LocalizedException
     */
    public function getSessionId(Wishlist $wishlist)
    {
        $customerId = $wishlist->getCustomerId();
        return $this->customerHandle->getCustomerSessionId((int)$customerId);
    }
}
