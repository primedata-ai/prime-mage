<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

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
     * Subscription constructor.
     * @param SyncConfig $synConfig
     * @param SyncHandle $syncHandle
     * @codeCoverageIgnore
     */
    public function __construct(
        SyncConfig $synConfig,
        SyncHandle $syncHandle
    ) {
        $this->synConfig = $synConfig;
        $this->syncHandle = $syncHandle;
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

            $this->syncHandle->synDataToPrime(self::EVENT_NAME, $dataNewsletters);
        }
    }
}
