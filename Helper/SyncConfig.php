<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class SyncConfig extends AbstractHelper
{
    const SYNC_CUSTOMER = 'prime_data_connect/sync_config/sync_customer';
    const SYNC_PRODUCT = 'prime_data_connect/sync_config/sync_customer';
    const SYNC_REVIEW = 'prime_data_connect/sync_config/sync_review';
    const SYNC_CART_ITEM = 'prime_data_connect/sync_config/sync_cart_item';
    const SYNC_SALES_ORDER = 'prime_data_connect/sync_config/sync_sales_order';

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isSyncCustomer()
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_CUSTOMER);
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isSyncProduct()
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_PRODUCT);
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isSyncReview()
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_REVIEW);
    }

    /**
     * @return bool
     */
    public function isSyncCartItem()
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_CART_ITEM);
    }

    /**
     * @return bool
     */
    public function isSyncOrder()
    {
        return $this->scopeConfig->isSetFlag(self::SYNC_SALES_ORDER);
    }
}
