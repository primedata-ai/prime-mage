<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Prime\Tracking\Target;
use PrimeData\PrimeDataConnect\Model\Tracking\PrimeTarget;

class ProductHandle
{
    const TYPE_TARGET = 'product';
    const SESSION_DEFAULT = 'admin-session-id';

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

        return $store->getBaseUrl() . $urlKey . '.html';
    }

    /**
     * @param ProductInterface $product
     * @return Target
     * @throws NoSuchEntityException
     */
    public function getProductSync(ProductInterface $product)
    {
        $target = new PrimeTarget();
        $target->setItemType(self::TYPE_TARGET);
        $target->setItemId((string)$product->getId());
        $target->setProperties($this->getProperties($product));
        return $target->createPrimeTarget();
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws NoSuchEntityException
     */
    private function getProperties(ProductInterface $product)
    {
        $cats = $product->getCategoryIds();
        $productId = $product->getId();
        $productSku = $product->getSku();
        $productName = $product->getName();
        $productCreatedAt = $product->getCreatedAt();
        $productType = $product->getTypeId();
        $productUrl = $this->getProductUrl($productSku);
        $productImage = $product->getImage();

        return [
            'product_name'  => $productName,
            'product_sku'   => $productSku,
            'created_at'    => $productCreatedAt,
            'product_type'  => $productType,
            'productUrl'    => $productUrl,
            'product_image' => $productImage,
            'product_price' => $this->getPriceDataProduct($product),
            'product_id'    => $productId,
            'category_ids'  => $cats
        ];
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPriceDataProduct(ProductInterface $product)
    {
        $priceData = $product->getPriceModel();
        $priceFinal = $priceData->getFinalPrice(1, $product);
        $priceOrigin = $product->getPrice();
        $currency = $this->storeManager->getStore()->getBaseCurrencyCode();

        return [
            'price_origin'   => $priceOrigin,
            'price_discount' => ($priceFinal < $priceOrigin) ? $priceFinal : null,
            'currency'       => $currency
        ];
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return self::SESSION_DEFAULT;
    }
}
