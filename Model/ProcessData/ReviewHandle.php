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

class ReviewHandle
{
    const TYPE_TARGET =  'review_product';
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
     * @var PrimeTarget
     */
    protected $target;

    /**
     * ReviewHandle constructor.
     * @param ProductRepository $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerHandle $customerHandle
     * @param PrimeTarget $target
     * @param StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        ProductRepository $productRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerHandle $customerHandle,
        PrimeTarget $target,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->customerHandle = $customerHandle;
        $this->target = $target;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Review $review
     * @return Target
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processReviewData(Review $review)
    {
        $this->target->setItemType(self::TYPE_TARGET);
        $this->target->setItemId($review->getId());

        $reviewData = [
            'review_title'   => $review->getTitle(),
            'review_nickname' => $review->getNickname(),
            'review_message' => $review->getDetail()
        ];
        $productId = $review->getEntityPkValue();
        $customerId = $review->getCustomerId() ?: 0;
        $reviewerInfo = $this->getCustomerData((int)$customerId);
        $reviewData = array_merge($reviewData, $reviewerInfo);
        $reviewProductData = $this->getProductInfo((int)$productId);
        $storeId = $this->storeManager->getStore()->getId();
        $reviewProductData['product_url'] = $review->getProductUrl((int)$productId, (int)$storeId);

        $properties = array_merge($reviewData, $reviewProductData);
        $this->target->setProperties($properties);
        return  $this->target->createPrimeTarget();
    }

    /**
     * @param int $productId
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductInfo(int $productId)
    {
        $product = $this->productRepository->getById($productId);
        return [
            'product_id' => $productId,
            'product_name' => $product->getName()
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
            'customer_id' => $customer->getId(),
            'customer_email' => $customer->getEmail()
        ];
    }

    /**
     * @param Review $review
     * @return string|null
     */
    public function getSessionId(Review $review)
    {
        $customerId = $review->getCustomerId() ?: 0;
        return $this->customerHandle->getCustomerSessionId((int)$customerId);
    }
}
