<?php

namespace PrimeData\PrimeDataConnect\Api;

interface SubscriptionInterface
{
    /**
     * Get customer name by Customer ID,email,is_subscribed,subscribe_at
     *
     * @param int $customerId
     * @param string $email
     * @param $isSubscribed
     * @param $subscribeAt
     * @return $this
     * @api
     */
    public function updateNewsletterStatus($customerId, $email, $isSubscribed, $subscribeAt);
}
