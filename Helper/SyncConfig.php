<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class SyncConfig extends AbstractHelper
{
    const SYNC_CUSTOMER = 'prime_data_connect/sync_config/sync_customer';
    const SYNC_PRODUCT = 'prime_data_connect/sync_config/sync_customer';

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
}
