<?php

namespace Zenfit\StripeBundle\Exceptions;

use Exception;
use Stripe\Subscription;

final class SubscriptionCreationFailed extends Exception
{
    private function __construct(private Subscription $subscription, string $message)
    {
        parent::__construct($message);
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public static function incomplete(Subscription $subscription): SubscriptionCreationFailed
    {
        return new self($subscription, "Subscription incomplete due to SCA.");
    }
}
