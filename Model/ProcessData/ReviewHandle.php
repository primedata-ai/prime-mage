<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;
use PrimeData\PrimeDataConnect\Model\UserInfo;

class ReviewHandle
{
    const TYPE_TARGET = 'product';
    /**
     * @var Review
     */
    protected $review;

    /**
     * @var ProductHandle
     */
    protected $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var CustomerHandle
     */
    protected $customerHandle;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ReviewHandle constructor.
     * @param ProductRepository $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerHandle $customerHandle
     * @param StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        ProductRepository $productRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerHandle $customerHandle,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->customerHandle = $customerHandle;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Review $review
     * @return Target
     * @throws NoSuchEntityException
     */
    public function getProductInfo(Review $review)
    {
        $target = new PrimeTarget();
        $target->setItemType(self::TYPE_TARGET);
        $productId = $review->getEntityPkValue();
        $target->setItemId((string)$productId);

        $product = $this->productRepository->getById($productId);
        $storeId = $this->storeManager->getStore()->getId();
        $properties = [
            'name' => $product->getName(),
            'url'  => $review->getProductUrl((int)$productId, (int)$storeId),
        ];
        $target->setProperties($properties);
        return $target->createPrimeTarget();
    }

    /**
     * @param Review $review
     * @return array
     */
    public function getReviewInfo(Review $review)
    {
        $target = new PrimeTarget();
        $target->setItemType(self::TYPE_TARGET);
        $target->setItemId($review->getId());

        return [
            'review_title'    => $review->getTitle(),
            'review_nickname' => $review->getNickname(),
            'review_message'  => $review->getDetail()
        ];
    }

    /**
     * @param int $customerId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getCustomerData(int $customerId)
    {
        if (!$customerId) {
            return [];
        }

        $customer = $this->customerRepository->getById($customerId);
        return [
            'customer_id'    => $customer->getId(),
            'customer_email' => $customer->getEmail()
        ];
    }

    /**
     * @param Review $review
     * @return UserInfo
     */
    public function getProfile(Review $review)
    {
        $customerId = $review->getCustomerId();
        return new UserInfo((int)$customerId, $this->customerHandle->getCustomerSessionId((int)$customerId));
    }
}
