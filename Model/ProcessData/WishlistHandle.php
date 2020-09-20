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
use PrimeData\PrimeDataConnect\Model\UserInfo;

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
        ProductHandle $productHandle
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerHandle = $customerHandle;
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
        $target = new PrimeTarget();
        $wishlistId = $wishlist->getId();
        $target->setItemType(self::TYPE_TARGET);
        $target->setItemId($wishlistId);
        $wishlistName = $wishlist->getName();

        $wishlistData['wishlist_name'] = $wishlistName;
        $customerData = $this->getCustomerData($wishlist);
        $itemData = $this->getWishlistItem($item);

        $properties = array_merge($wishlistData, $customerData, $itemData);
        $target->setProperties($properties);

        return $target->createPrimeTarget();
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
     * @return UserInfo
     */
    public function getProfile(Wishlist $wishlist)
    {
        $customerId = $wishlist->getCustomerId();
        return new UserInfo($customerId, $this->customerHandle->getCustomerSessionId((int)$customerId));
    }
}
