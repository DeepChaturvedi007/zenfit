<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Services\StripeHook\PaymentFailedHookService;
use AppBundle\Services\StripeHook\PaymentRefundedHookService;
use AppBundle\Services\StripeHook\PaymentSucceededHookService;
use AppBundle\Services\StripeHook\PayoutHookService;
use AppBundle\Services\StripeHook\SubscriptionCreatedHookService;
use AppBundle\Services\StripeHook\SubscriptionDeletedHookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class HookController extends AbstractController
{
    private SubscriptionDeletedHookService $subscriptionDeletedHookService;
    private PaymentRefundedHookService $paymentRefundedHookService;
    private PaymentFailedHookService $paymentFailedHookService;
    private PaymentSucceededHookService $paymentSucceededHookService;
    private SubscriptionCreatedHookService $subscriptionCreatedHookService;
    private PayoutHookService $payoutHookService;

    public function __construct(
        SubscriptionDeletedHookService $subscriptionDeletedHookService,
        PaymentFailedHookService $paymentFailedHookService,
        SubscriptionCreatedHookService $subscriptionCreatedHookService,
        PayoutHookService $payoutHookService,
        PaymentSucceededHookService $paymentSucceededHookService,
        PaymentRefundedHookService $paymentRefundedHookService
    ) {
        $this->subscriptionCreatedHookService = $subscriptionCreatedHookService;
        $this->subscriptionDeletedHookService = $subscriptionDeletedHookService;
        $this->paymentRefundedHookService = $paymentRefundedHookService;
        $this->paymentFailedHookService = $paymentFailedHookService;
        $this->payoutHookService = $payoutHookService;
        $this->paymentSucceededHookService = $paymentSucceededHookService;
    }

    private static $STRIPE_ACCOUNT;

    public function handleAction(Request $request): JsonResponse
    {
        //if request is from our Stripe account, acc = 'zenfit'
        //if request is from a trainer account, acc = 'connect'
        self::$STRIPE_ACCOUNT = $request->query->get('acc');
        $payload = json_decode($request->getContent());

        $type = (string) $payload->type;
        match ($type) {
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($payload),
            'invoice.payment_failed' => $this->handlePaymentFailed($payload),
            'customer.subscription.created' => $this->handleSubscriptionCreated($payload),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
            'charge.refunded' => $this->handleRefund($payload),
            'charge.succeeded' => $this->handleChargeSucceeded($payload),
            'payout.created' => $this->handlePayout($payload),
            'payout.paid' => $this->handlePayout($payload),
            default => null,
        };

        return new JsonResponse([]);
    }

    // subscription  is finished

    private function handleSubscriptionDeleted(object $payload): void
    {
        $subscriptionDeleted = $this->subscriptionDeletedHookService;
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'customer')
        ) {
            throw new \RuntimeException();
        }

        $object = $payload->data->object;
        $customer = $object->customer;

        $subscriptionDeleted
            ->setStripeAccount(self::$STRIPE_ACCOUNT)
            ->setCustomer($customer)
            ->setType($payload->type)
            ->setCurrentPeriodEnd(time())
            ->setCanceled(1)
            ->setCanceledAt(time())
            ->insert();
    }

    private function handleRefund(object $payload): void
    {
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'customer')
            || !property_exists($payload->data->object, 'currency')
            || !property_exists($payload->data->object, 'amount_refunded')
            || !property_exists($payload->data->object, 'application_fee_amount')
        ) {
            throw new \RuntimeException();
        }

        $paymentRefunded = $this->paymentRefundedHookService;
        $object = $payload->data->object;

        if ($object->customer !== null) {
            $paymentRefunded
                ->setCustomer($object->customer)
                ->setCurrency($object->currency)
                ->setAmount($object->amount_refunded / 100)
                ->setType($payload->type)
                ->setApplicationFee($object->application_fee_amount / 100)
                ->insert();
        }
    }

    private function handlePaymentFailed(object $payload): void
    {
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'customer')
            || !property_exists($payload->data->object, 'amount_due')
            || !property_exists($payload->data->object, 'currency')
            || !property_exists($payload->data->object, 'attempt_count')
            || !property_exists($payload->data->object, 'next_payment_attempt')
            || !property_exists($payload->data->object, 'hosted_invoice_url')
            || !property_exists($payload->data->object, 'account_country')
        ) {
            throw new \RuntimeException();
        }

        $paymentFailed = $this->paymentFailedHookService;
        $object = $payload->data->object;
        $paymentFailed
            ->setStripeAccount(self::$STRIPE_ACCOUNT)
            ->setType($payload->type)
            ->setCustomer($object->customer)
            ->setAmount($object->amount_due / 100)
            ->setCurrency($object->currency)
            ->setAttemptCount($object->attempt_count)
            ->setNextPaymentAttempt($object->next_payment_attempt)
            ->setInvoiceUrl($object->hosted_invoice_url)
            ->setAccountCountry($object->account_country)
            ->insert();
    }

    private function handlePaymentSucceeded(object $payload): void
    {
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'customer')
            || !property_exists($payload->data->object, 'amount_paid')
            || !property_exists($payload->data->object, 'application_fee_amount')
            || !property_exists($payload->data->object, 'currency')
            || !property_exists($payload->data->object, 'charge')
        ) {
            throw new \RuntimeException();
        }

        $paymentSucceeded = $this->paymentSucceededHookService;
        $object = $payload->data->object;

        $paymentSucceeded
            ->setStripeAccount(self::$STRIPE_ACCOUNT)
            ->setType($payload->type)
            ->setCustomer($object->customer)
            ->setAmount($object->amount_paid / 100)
            ->setApplicationFee($object->application_fee_amount / 100)
            ->setCurrency($object->currency)
            ->setCharge($object->charge)
            ->insert();
    }

    private function handleChargeSucceeded(object $payload): void
    {
        if (!property_exists($payload, 'data') ||
            !is_object($payload->data) ||
            !property_exists($payload->data, 'object') ||
            !is_object($payload->data->object)
        ) {
            throw new \RuntimeException();
        }

        $paymentSucceeded = $this->paymentSucceededHookService;
        $object = $payload->data->object;
        if (property_exists($object, 'source') &&
            is_object($object->source) &&
            property_exists($object->source, 'type') &&
            $object->source->type === 'klarna'
        ) {
            if (!property_exists($payload, 'type')
                || !property_exists($object, 'customer')
                || !property_exists($object, 'amount')
                || !property_exists($object, 'application_fee_amount')
                || !property_exists($object, 'currency')
            ) {
                throw new \RuntimeException();
            }

            $paymentSucceeded
                ->setStripeAccount(self::$STRIPE_ACCOUNT)
                ->setType($payload->type)
                ->setCustomer($object->customer)
                ->setAmount($object->amount / 100)
                ->setApplicationFee($object->application_fee_amount / 100)
                ->setCurrency($object->currency)
                ->setCharge(null)
                ->insert();
        }
    }

    private function handleSubscriptionCreated(object $payload): void
    {
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'customer')
        ) {
            throw new \RuntimeException();
        }

        $subscriptionCreated = $this->subscriptionCreatedHookService;
        $object = $payload->data->object;
        $subscriptionCreated
            ->setStripeAccount(self::$STRIPE_ACCOUNT)
            ->setType($payload->type)
            ->setCustomer($object->customer)
            ->insert();
    }

    private function handlePayout(object $payload): void
    {
        if (!property_exists($payload, 'data')
            || !property_exists($payload, 'type')
            || !property_exists($payload, 'account')
            || !is_object($payload->data)
            || !property_exists($payload->data, 'object')
            || !is_object($payload->data->object)
            || !property_exists($payload->data->object, 'currency')
            || !property_exists($payload->data->object, 'amount')
            || !property_exists($payload->data->object, 'arrival_date')
        ) {
            throw new \RuntimeException();
        }

        $payoutService = $this->payoutHookService;
        $object = $payload->data->object;
        $payoutService
            ->setStripeAccount(self::$STRIPE_ACCOUNT)
            ->setType($payload->type)
            ->setCurrency($object->currency)
            ->setAmount($object->amount / 100)
            ->setArrivalDate($object->arrival_date)
            ->setUserStripeAccount($payload->account)
            ->insert();
    }
}
