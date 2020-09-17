<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;

class WishlistHandle
{
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
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processWishlistData(Wishlist $wishlist, Item $item)
    {
        $wishlistId = $wishlist->getId();
        $wishlistName = $wishlist->getName();
        $wishlistData['wishlist_id'] = $wishlistId;
        $wishlistData['wishlist_name'] = $wishlistName;
        $customerData = $this->getCustomerData($wishlist);
        $itemData = $this->getWishlistItem($item);

        return array_merge($wishlistData, $customerData, $itemData);
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
        $productData = $this->productHandle->processProductSync($product);
        $itemData['item_id'] = $item->getId();
        $itemData['item_qty'] = $product->getQty();
        return array_merge($itemData, $productData);
    }
}
