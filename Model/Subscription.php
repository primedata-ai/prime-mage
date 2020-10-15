<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use Magento\Framework\Stdlib\CookieManagerInterface;
use PrimeData\PrimeDataConnect\Api\SubscriptionInterface;
use PrimeData\PrimeDataConnect\Helper\MessageQueue\SyncHandle;
use PrimeData\PrimeDataConnect\Helper\SyncConfig;

class Subscription implements SubscriptionInterface
{
    const EVENT_NAME = 'customer_subscribe_newsletter';
    /**
     * @var SyncConfig
     */
    protected $synConfig;
    /**
     * @var SyncHandle
     */
    protected $syncHandle;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Subscription constructor.
     * @param SyncConfig $synConfig
     * @param SyncHandle $syncHandle
     * @param CookieManagerInterface $cookieManager
     * @codeCoverageIgnore
     */
    public function __construct(
        SyncConfig $synConfig,
        SyncHandle $syncHandle,
        CookieManagerInterface $cookieManager
    ) {
        $this->synConfig = $synConfig;
        $this->syncHandle = $syncHandle;
        $this->cookieManger = $cookieManager;
    }

    /**
     * @inheritDoc
     * @throws \ErrorException
     */
    public function updateNewsletterStatus($customerId, $email, $isSubscribed, $subscribeAt)
    {
        if ($this->synConfig->isSyncCustomer()) {
            $dateTime = strtotime($subscribeAt);
            $subscribeAt  = date("Y-m-d H:i:s", $dateTime);
            $dataNewsletters = [
                'customerId' => $customerId,
                'email'      => $email,
                'is_subscribed' => $isSubscribed,
                'subscribe_at'  => $subscribeAt
            ];
            $cookieXSessionId = $this->cookieManger->getCookie('XSessionId');
            $sessionId = ($cookieXSessionId)? $cookieXSessionId : "";

            $this->syncHandle->synDataToPrime(self::EVENT_NAME, (string) $customerId, null, $dataNewsletters, $sessionId);
        }
    }
}
