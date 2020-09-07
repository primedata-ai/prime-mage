<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model;

use PrimeData\PrimeDataConnect\Api\SubscriptionInterface;

class Subscription implements SubscriptionInterface
{
    /**
     * @inheritDoc
     */
    public function updateNewsletterStatus($customerId, $email, $isSubscribed, $subscribeAt)
    {
        // TODO: Implement updateNewsletterStatus() method.
    }
}
