<?php

namespace AppBundle\Services;

use Stripe;

class StripeService
{
    public $stripe;
    private $options = [];

    public function __construct(
        string $stripeSecretKey,
        string $stripeApiVersion
    ) {
        $this->stripe = new \Stripe\StripeClient([
            'api_key' => $stripeSecretKey,
            'stripe_version' => $stripeApiVersion
        ]);
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function createIntent($customer)
    {
        return $this->stripe->setupIntents->create([
            'customer' => $customer,
            'payment_method_types' => ['card']
        ], $this->options);
    }

    public function retrieveIntent($id)
    {
        return $this->stripe->setupIntents->retrieve($id, [], $this->options);
    }

    public function retrieveCustomer($customerId)
    {
        return $this->stripe->customers->retrieve($customerId, [], $this->options);
    }

    public function createCustomer($email, $name, $coupon = null)
    {
        return $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
            'coupon' => $coupon,
        ], $this->options);
    }

    public function retrieveCoupon($couponId)
    {
        return $this->stripe->coupons->retrieve($couponId, [], $this->options);
    }

    public function createProduct($name)
    {
        return $this->stripe->products->create([
            'name' => $name,
        ], $this->options);
    }

    public function createPrice($params)
    {
        return $this->stripe->prices->create($params, $this->options);
    }

    public function retrieveSubscription($subscriptionId)
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId, [], $this->options);
    }

    public function updateSubscription($subscriptionId, $params)
    {
        return $this->stripe->subscriptions->update($subscriptionId, $params, $this->options);
    }

    public function createSubscription($params)
    {
        return $this->stripe->subscriptions->create($params, $this->options);
    }

    public function unsubscribeSubscription($subscriptionId): bool
    {
        try {
            $subscription = $this->retrieveSubscription($subscriptionId);
            $subscription->cancel();
        } catch (Stripe\Exception\InvalidRequestException $e) {
            if (str_contains($e->getMessage(), 'No such subscription')) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    public function updatePaymentDate(string $subscriptionId, string $trialEnd): void
    {
        $this->updateSubscription($subscriptionId, [
            'trial_end' => $trialEnd,
            'proration_behavior' => 'none'
        ]);
    }

    public function getPaymentMethod(string $paymentMethod): ?\Stripe\PaymentMethod
    {
        return $this->stripe->paymentMethods->retrieve($paymentMethod, [], $this->options);
    }

    public function updatePaymentMethod($paymentMethod, $customerId)
    {
        $this->stripe->paymentMethods->attach($paymentMethod, [
            'customer' => $customerId
        ], $this->options);

        $customer = $this->retrieveCustomer($customerId);
        $customer->invoice_settings = ['default_payment_method' => $paymentMethod];
        $customer->save();
    }

    public function getClientSecretFromPaymentIntent($invoiceId)
    {
        $invoice = $this->stripe->invoices->retrieve($invoiceId, [], $this->options);
        $paymentIntent = $this->stripe->paymentIntents->retrieve($invoice->payment_intent, [], $this->options);
        return $paymentIntent->client_secret;
    }

    public function createInvoiceItem($amount, $currency, $customer, $description)
    {
        //check for existing invoice item
        $existingInvoiceItems = $this->stripe->invoiceItems->all([
            'customer' => $customer->id,
            'pending' => true
        ], $this->options);

        if (count($existingInvoiceItems->data) !== 0) {
            return;
        }

        $invoiceItem = [
            'customer' => $customer->id,
            'amount' => $amount * 100,
            'currency' => $currency,
            'description' => $description,
        ];

        return $this->stripe->invoiceItems->create($invoiceItem, $this->options);
    }

    public function createInvoice($params)
    {
        return $this->stripe->invoices->create($params, $this->options);
    }

    /** @return \Stripe\Collection<int, mixed> */
    public function getInvoices(string $customerId, ?string $status = null): \Stripe\Collection
    {
        $params = ['customer' => $customerId];
        if ($status !== null) {
            $params['status'] = $status;
        }

        return $this->stripe->invoices->all($params, $this->options);
    }

    public function deleteInvoiceItems($customer)
    {
        $invoiceItems = $this->stripe->invoiceItems->all([
            'customer' => $customer,
            'pending' => true
        ], $this->options);

        foreach ($invoiceItems->data as $invoiceItem) {
            if (strpos($invoiceItem->description, 'Unused') !== false||strpos($invoiceItem->description, 'Remaining') !== false) {
                $invoiceItem->delete();
            }
        }
    }

    public function createSession($customer, $successUrl, $cancelUrl)
    {
        return $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'setup',
            'customer' => $customer,
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl
        ], $this->options);
    }

    public function retrieveSession($id)
    {
        return $this->stripe->checkout->sessions->retrieve($id, [], $this->options);
    }

    /** @param array<string, mixed> $params */
    public function createCharge(array $params): \Stripe\Charge
    {
        return $this->stripe->charges->create($params, $this->options);
    }

    public function retrieveCharge(string $chargeId): ?\Stripe\Charge
    {
        return $this->stripe->charges->retrieve($chargeId, [], $this->options);
    }

    /** @param array<string, mixed> $params */
    public function refund(array $params): \Stripe\Refund
    {
        return $this->stripe->refunds->create($params, $this->options);
    }
}
