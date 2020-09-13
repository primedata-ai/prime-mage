<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductHandle
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ProductHandle constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $productSku
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getProductUrl(string $productSku)
    {
        $store = $this->storeManager->getStore();
        $title = explode('-', $productSku);
        $urlKey = preg_replace('~[^\pL\d]+~u', '-', $title[0]);
        $urlKey = iconv('utf-8', 'us-ascii//TRANSLIT', $urlKey);
        $urlKey = preg_replace('~[^-\w]+~', '', $urlKey);
        $urlKey = trim($urlKey, '-');
        $urlKey = preg_replace('~-+~', '-', $urlKey);
        $urlKey = strtolower($urlKey);

        $productUrl = $store->getBaseUrl() . $urlKey . '.html';
        return $productUrl;
    }

    /**
     * @param Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function processProductSync(Product $product) :array
    {
        $cats = $product->getCategoryIds();
        $productId = $product->getId();
        $productSku = $product->getSku();
        $productName = $product->getName();
        $productCreatedAt = $product->getCreatedAt();
        $productType = $product->getTypeId();
        $productUrl = $this->getProductUrl($productSku);
        $productImage = $product->getImage();
        return  [
            'product_name' => $productName,
            'product_sku'  => $productSku,
            'created_at'   => $productCreatedAt,
            'product_type' => $productType,
            'productUrl'   => $productUrl,
            'product_image' => $productImage,
            'product_price' => $product->getPrice(),
            'product_id'   => $productId,
            'category_ids' => $cats
        ];
    }
}
