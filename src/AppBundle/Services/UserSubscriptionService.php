<?php declare(strict_types=1);

namespace AppBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\UserSubscription;
use AppBundle\Entity\User;
use Zenfit\StripeBundle\Exceptions\SubscriptionCreationFailed;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserSubscriptionService
{
    private EntityManagerInterface $em;
    private \Stripe\Customer $customer;
    private string $taxRateId;
    private $trialEnd;
    private ?string $price = null;
    private User $user;

    private StripeService $stripeService;
    private string $appHostname;
    private string $stripeDKTaxId;
    private string $stripeNOTaxId;

    public function __construct(
        EntityManagerInterface $em,
        StripeService $stripeService,
        string $appHostname,
        string $stripeDKTaxId,
        string $stripeNOTaxId
    ) {
        $this->em = $em;
        $this->stripeService = $stripeService;
        $this->appHostname = $appHostname;
        $this->stripeDKTaxId = $stripeDKTaxId;
        $this->stripeNOTaxId = $stripeNOTaxId;
    }

    public function setCustomer(string $customerId, $paymentMethod): self
    {
        $customer = $this->stripeService->retrieveCustomer($customerId);
        $customer->invoice_settings = ['default_payment_method' => $paymentMethod];
        $customer->save();
        $this->customer = $customer;
        return $this;
    }

    public function setTaxRateId(string $taxRateId): self
    {
        $this->taxRateId = $taxRateId;
        return $this;
    }

    public function setTrialEnd($trialEnd): self
    {
        $this->trialEnd = $trialEnd;
        return $this;
    }

    public function setPrice(?string $price = null): self
    {
        $this->price = $price;
        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /** @param array<string, mixed> $taxArray */
    public function createCustomer(string $name, string $email, array $taxArray): \Stripe\Customer
    {
        $customer = $this->stripeService->createCustomer($email, $name);

        if ($taxArray['create_tax_id'] === true) {
            $this->updateCustomerTaxInfo($taxArray['tax_id'], $customer);
        }

        $this->customer = $customer;
        return $customer;
    }

    public function updateCustomerTaxInfo(string $vat, ?\Stripe\Customer $customer = null): void
    {
        $userSubscription = $this->user->getUserSubscription();

        if ($userSubscription === null) {
            return;
        }

        $subscription = $userSubscription->getSubscription();
        if ($subscription === null) {
            return;
        }

        if ($customer === null) {
            $customer = $this->stripeService->retrieveCustomer($userSubscription->getStripeCustomer());
        }

        $exempt = match($subscription->getCountry()) {
            'eu' => 'reverse',
            'dk' => 'none',
            default => 'exempt',
        };

        try {
            $customer->tax_exempt = $exempt;
            $this->stripeService->stripe->customers->createTaxId($customer->id,
                ['type' => 'eu_vat', 'value' => $vat]
            );
        } catch (\Throwable $e) {}
        $customer->save();
    }

    public function createIntent(): \Stripe\SetupIntent
    {
        return $this->stripeService->createIntent($this->customer);
    }

    public function getPaymentIntentClientSecretFromInvoice($invoiceId): string
    {
        return $this->stripeService->getClientSecretFromPaymentIntent($invoiceId);
    }

    public function subscribe(UserSubscription $userSubscription): \Stripe\Subscription
    {
        if ($userSubscription->getSubscription() === null) {
            throw new BadRequestHttpException('You need a subscription in order to continue.');
        }

        //create the subscription
        $upfrontArray = [];
        $upfront = $userSubscription->getSubscription()->getUpfrontFee();
        $currency = $userSubscription->getSubscription()->getCurrency();

        //set tax rate id
        $taxRateId = $this->stripeNOTaxId;
        $countries = ['eu', 'dk'];
        if (in_array($userSubscription->getSubscription()->getCountry(), $countries)) {
            $taxRateId = $this->stripeDKTaxId;
        }
        $this->setTaxRateId($taxRateId);

        if ($upfront > 0) {
            if ($this->trialEnd != 'now') {
                $subscription = $this->stripeService->createSubscription($this->buildPayload());
                $this->stripeService->createInvoiceItem($upfront, $currency, $this->customer, 'Upfront fee');
            } else {
                $this->stripeService->createInvoiceItem($upfront, $currency, $this->customer, 'Upfront fee');
                $subscription = $this->stripeService->createSubscription($this->buildPayload());
            }
        } else {
            $subscription = $this->stripeService->createSubscription($this->buildPayload());
        }

        if ($subscription->status === 'incomplete') {
            throw SubscriptionCreationFailed::incomplete($subscription);
        }

        return $subscription;
    }

    public function updateUserDetails(string $subscriptionId, string $customerId, User $user): void
    {
        if ($user->getUserSubscription() !== null) {
            $user
                ->getUserSubscription()
                ->setStripeSubscription($subscriptionId)
                ->setStripeCustomer($customerId)
                ->setSubscribedDate(new \DateTime('now'))
                ->setCanceled(false)
                ->setCanceledAt(null);

            $user->setActivated(true);
            $this->em->flush();
        }
    }

    /** @return array<mixed> */
    public function getInvoicesByUser(string $customerId): array
    {
        $invoices = $this
            ->stripeService
            ->getInvoices($customerId);

        return collect($invoices->data)->map(function(\Stripe\Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'date' => \Carbon\Carbon::createFromTimestamp($invoice->created)->format('Y-m-d'),
                'amount' => $invoice->amount_due / 100,
                'currency' => $invoice->currency,
                'url' => $invoice->hosted_invoice_url,
                'status' => $invoice->status
            ];
        })->toArray();
    }

    /** @return array<mixed> */
    public function getDefaultCard(string $customerId): array
    {
        $defaultCard = [];
        $customer = $this
            ->stripeService
            ->retrieveCustomer($customerId);

        if ($customer !== null) {
            $paymentMethod = $this
                ->stripeService
                ->getPaymentMethod($customer->invoice_settings->default_payment_method);

            if ($paymentMethod !== null && isset($paymentMethod->card->brand, $paymentMethod->card->last4)) {
                $defaultCard = [
                    'brand' => $paymentMethod->card->brand,
                    'last4' => $paymentMethod->card->last4
                ];
            }
        }

        return $defaultCard;
    }

    public function updateCard(string $sessionId): void
    {
        $session = $this
            ->stripeService
            ->retrieveSession($sessionId);

        $setupIntent = $this->stripeService->retrieveIntent($session->setup_intent);
        $this->stripeService->updatePaymentMethod($setupIntent->payment_method, $this->user->getUserSubscription()->getStripeCustomer());
    }

    public function createSession(): \Stripe\Checkout\Session
    {
        return $this->stripeService->createSession(
            $this->user->getUserSubscription()->getStripeCustomer(),
            $this->appHostname . '/settings/update-card-success',
            $this->appHostname . '/settings'
        );
    }

    protected function buildPayload(): array
    {
        return [
            'items' => [['price' => $this->price]],
            'default_tax_rates' => [$this->taxRateId],
            'trial_end' => $this->trialEnd,
            'customer' => $this->customer,
            'off_session' => true,
            'payment_behavior' => 'allow_incomplete'
        ];
    }
}
