<?php

namespace AppBundle\Services;

use AppBundle\Entity\StripeConnect;
use AppBundle\Entity\UserStripe;
use AppBundle\Entity\Payment;
use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\Event;
use AppBundle\Entity\Client;
use AppBundle\Repository\PaymentRepository;
use AppBundle\Repository\ClientStripeRepository;
use AppBundle\Event\ClientMadeChangesEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use LogicException;
use Stripe;
use Carbon\Carbon;
use Throwable;
use UnexpectedValueException;
use Stripe\Exception\InvalidRequestException;
use Zenfit\StripeBundle\Exceptions\SubscriptionCreationFailed;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StripeConnectService
{
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $em;
    private PaymentRepository $paymentRepository;
    private ClientStripeRepository $clientStripeRepository;

    private ?UserStripe $userStripe = null;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Stripe\SetupIntent
     */
    private $intent;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Stripe\Price
     */
    private $price;

    /**
     * @var Stripe\Customer
     */
    private $customer;

    /**
    * @var string $defaultPaymentMethod
    */
    private $defaultPaymentMethod;

    private QueueService $queueService;
    private TrainerAssetsService $trainerAssetsService;
    private StripeService $stripeService;
    private string $appHostname;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $em,
        PaymentRepository $paymentRepository,
        ClientStripeRepository $clientStripeRepository,
        QueueService $queueService,
        TrainerAssetsService $trainerAssetsService,
        StripeService $stripeService,
        string $appHostname
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
        $this->clientStripeRepository = $clientStripeRepository;
        $this->queueService = $queueService;
        $this->trainerAssetsService = $trainerAssetsService;
        $this->stripeService = $stripeService;
        $this->appHostname = $appHostname;
    }

    public function getCustomer(): Stripe\Customer
    {
        return $this->customer;
    }

    public function setCustomer($customerId): void
    {
        $customer = $this->stripeService->retrieveCustomer($customerId);
        $customer->invoice_settings = ['default_payment_method' => $this->defaultPaymentMethod];
        $customer->name = $this->client->getName();
        $customer->email = $this->client->getEmail();
        $customer->save();
        $this->customer = $customer;
    }

    public function setUserStripe(UserStripe $userStripe): self
    {
        $this->userStripe = $userStripe;
        $this->stripeService->setOptions($this->getUserStripeAccount());

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserStripeId()
    {
        if ($this->userStripe) {
            return $this->userStripe->getStripeUserId();
        }
        return null;
    }

    /**
     * @param string $defaultPaymentMethod
     *
     * @return StripeConnectService
     */
    public function setDefaultPaymentMethod($defaultPaymentMethod)
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;
        return $this;
    }

    /**
     * @param Client $client
     *
     * @return StripeConnectService
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param Payment $payment
     *
     * @return StripeConnectService
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return ClientStripe|null
     */
    public function getClientStripeEntity()
    {
        return $this
            ->clientStripeRepository
            ->findOneBy([
                'client' => $this->client->getId()
            ], ['id' => 'DESC']);
    }

    /**
     * @return int
     */
    public function getStripeUserFeePercentage()
    {
        return rescue(function () {
            if ($this->userStripe === null) {
                throw new \RuntimeException();
            }

            return $this->userStripe->getFeePercentage();
        }, 0);
    }

    /**
     * @return int
     */
    public function getApplicationFeeRequired()
    {
        return rescue(function () {
            if ($this->userStripe === null) {
                throw new \RuntimeException();
            }

            return (int)$this->userStripe->getApplicationFeeRequired();
        }, 0);
    }

    /**
     * @return Stripe\SetupIntent
     */
    public function createIntent()
    {
        return $this->stripeService->createIntent($this->customer);
    }

    private function chargeUpfrontFee(): void
    {
        $user = $this->client->getUser();
        $companyName = $this->trainerAssetsService->getUserSettings($user)->getCompanyName() ?? $user->getName();
        $description = 'One-time upfront fee to ' . $companyName ?? $user->getName();

        $this->stripeService->createInvoiceItem(
            $this->payment->getUpfrontFee(),
            $this->payment->getCurrency(),
            $this->getCustomer(),
            $description
        );
    }

    public function initiateSubscription(): Stripe\Subscription
    {
        //depending on when client should pay upfront fee
        //the order of subscription creation and upfront fee is important
        //if client is NOT due to pay yet - eg. has a trialEnd
        //subscription needs to be created first
        //and then upfront fee is added
        //if client IS DUE TO pay, it needs to be reverse order

        if ($this->payment->getUpfrontFee() > 0) {
            //if there is an upfront fee
            if (!$this->payment->getDelayUpfront()) {
                //upfront fee should be charged immediately
                $this->chargeUpfrontFee();
                $subscription = $this->createPrice()->subscribe();
            } else {
                //upfront fee should be charged at the same time as subscription
                $subscription = $this->createPrice()->subscribe();
                $this->chargeUpfrontFee();
            }
        } else {
            //no upfront fee
            $subscription = $this->createPrice()->subscribe();
        }

        if ($subscription->status === 'incomplete') {
            throw SubscriptionCreationFailed::incomplete($subscription);
        }

        return $subscription;
    }

    public function createCustomer(?Stripe\Coupon $coupon = null): Stripe\Customer
    {
        $clientStripe = $this->getClientStripeEntity();

        $customer = rescue(function () use ($clientStripe) {
            if ($clientStripe === null) {
                throw new \RuntimeException('No ClientStripe entity');
            }
            $customer = null;
            //if customer has not yet been subscribed to anything
            if ($clientStripe->getStripeSubscription()) {
                $customer = $this->stripeService->retrieveCustomer($clientStripe->getStripeCustomer());
            }
            //if existing payment
            //check if currencies are the same
            //else Stripe will throw an exception
            $clientStripePayment = $clientStripe->getPayment();
            if ($clientStripePayment === null) {
                throw new \RuntimeException('No Payment in ClientStripe');
            }
            if (strtolower($clientStripePayment->getCurrency()) === strtolower($this->payment->getCurrency())) {
                $customer = $this->stripeService->retrieveCustomer($clientStripe->getStripeCustomer());
            }

            return $customer;
        }, null);

        if (!$customer) {
            $customer = $this->stripeService->createCustomer(
                $this->client->getEmail(),
                $this->client->getName(),
                $coupon && $coupon->valid ? $coupon->id : null
            );

            if (!$clientStripe) {
                $clientStripe = (new ClientStripe())
                    ->setClient($this->client);

                $this->em->persist($clientStripe);
            }

            $clientStripe->setStripeCustomer($customer->id);
            $this->em->flush();
        }

        $this->customer = $customer;
        return $customer;
    }

    public function createCharge(string $source): \Stripe\Charge
    {
        $amount = (int) $this->payment->getRecurringFee() * (int) $this->payment->getMonths() + (int) $this->payment->getUpfrontFee();
        $params = [
            'amount' => $amount * 100,
            'currency' => $this->payment->getCurrency(),
            'customer' => $this->customer,
            'source' => $source,
            'application_fee_amount' => $this->applyApplicationFee() * $amount
        ];

        return $this->stripeService->createCharge($params);
    }

    /**
     * @return StripeConnectService
     */
    public function createPrice()
    {
        $user = $this->client->getUser();
        $feeTo = $this->trainerAssetsService->getUserSettings($user)->getCompanyName() ?? $user->getName();
        $name = $feeTo . ' for ' . $this->client->getName();
        $product = $this->stripeService->createProduct($name);

        $args = [
            'currency' => $this->payment->getCurrency(),
            'unit_amount' => (int)$this->payment->getRecurringFee() * 100,
            'recurring' => ['interval' => 'month'],
            'product' => $product,
        ];

        $this->price = $this->stripeService->createPrice($args);
        return $this;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function subscribe()
    {
        $clientStripe = $this->getClientStripeEntity();
        $months = (int)$this->payment->getMonths();
        $trialEnd = $this->payment->getTrialEnd();

        $subscription = $this->subscribeClient();

        $periodEnd = match ($months) {
            0 => Carbon::now()->addMonths(1)->timestamp,
            default => Carbon::now()->addMonths($months)->timestamp,
        };

        if ($trialEnd > 0 && $trialEnd > Carbon::now()->timestamp) {
            $periodEnd = Carbon::createFromTimestamp($trialEnd)->addMonths($months)->timestamp;
        }

        $clientStripe
            ->setStripePlan($this->price->id)
            ->setStripeSubscription($subscription ? $subscription->id : null)
            ->setCurrentPeriodStart($subscription ? $subscription->current_period_start : null)
            ->setPeriodEnd((string) $periodEnd)
            ->setCanceled(0)
            ->setCanceledAt(null);

        $this->em->flush();

        return $subscription;
    }

    private function subscribeClient()
    {
        $subscription = null;
        $clientStripe = $this->getClientStripeEntity();
        $trialEnd = $this->payment->getTrialEnd();
        $paymentId = $this->payment->getId();

        if ($clientStripe === null) {
            throw new \RuntimeException('No Client stripe');
        }

        if ($trialEnd < Carbon::now()->timestamp) {
            $trialEnd = null;
        }

        $default_params = [
            'application_fee_percent' => $this->applyApplicationFee(),
            'off_session' => true,
            'payment_behavior' => 'allow_incomplete'
        ];

        if ($trialEnd) {
            $default_params['trial_end'] = $trialEnd;
        }

        if ($this->clientHasActiveSubscription($clientStripe)) {
            $subscription = $this->stripeService->retrieveSubscription($clientStripe->getStripeSubscription());

            $params = [
                'proration_behavior' => 'none',
                'items' => [[
                  'id' => $subscription->items->data[0]->id,
                  'price' => $this->price->id
                ]]
            ];

            $params = array_merge($params, $default_params);
            $this->stripeService->updateSubscription($clientStripe->getStripeSubscription(), $params);
        } else {
            $params = [
                'items' => [['price' => $this->price->id]],
                'customer' => $this->customer->id
            ];

            //cancel old subscription (if there are any in other currencies)
            if ($existingSubscription = $clientStripe->getStripeSubscription()) {
                $existingSub = $this->stripeService->retrieveSubscription($existingSubscription);
                if ($existingSub && $existingSub->status === 'active') {
                    $existingSub->cancel();
                }
            }

            $params = array_merge($params, $default_params);
            $subscription = $this->stripeService->createSubscription($params);
        }

        return $subscription;
    }

    public function getPaymentIntentClientSecretFromInvoice($invoiceId)
    {
        return $this->stripeService->getClientSecretFromPaymentIntent($invoiceId);
    }

    public function handleClientPaymentSuccessful(): void
    {
        $this->payment->setCharged(true);
        $clientStripe = $this->getClientStripeEntity();

        if ($clientStripe !== null) {
            $clientStripe->setPayment($this->payment);
        }

        //dispatch event that client has successfully paid
        $event = new ClientMadeChangesEvent($this->client, Event::PAYMENT_SUCCEEDED);
        $this->eventDispatcher->dispatch($event, Event::PAYMENT_SUCCEEDED);

        //delete any eventual pending payment links
        $unpaidLinks = $this
            ->paymentRepository
            ->findUnpaidLinksByClient($this->client);

        foreach ($unpaidLinks as $unpaidLink) {
            //skip just paid link
            if ($unpaidLink->getId() === $this->payment->getId()) {
                continue;
            }

            $unpaidLink->setDeleted(true);
        }

        $this->client->setAcceptTerms(true);
        $this->em->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Throwable
     */
    public function unsubscribeClient()
    {
        $clientStripe = $this->getClientStripeEntity();

        if (!$this->userStripe) {
            return;
        }

        $this
            ->stripeService
            ->unsubscribeSubscription($clientStripe->getStripeSubscription());

        $clientStripe
            ->setCanceled(1)
            ->setCanceledAt((string) time());

        $this->em->flush();
    }

     public function updatePaymentDate(string $trialEnd, bool $pause): void
     {
         $clientStripe = $this->getClientStripeEntity();

         $stripeSubscription = $clientStripe->getStripeSubscription();
         if (!$stripeSubscription) {
             throw new BadRequestHttpException('No Stripe subscription.');
         }

         $this->stripeService->updatePaymentDate($stripeSubscription, $trialEnd);

         if ($pause) {
             $clientStripe
                ->setPaused(true)
                ->setPausedUntil($trialEnd);
             $this->em->flush();
         }
     }

     public function refundClient(string $chargeId): void
     {
          //check if any application fee on the chargeId
          $refundApplicationFee = false;
          $charge = $this->stripeService->retrieveCharge($chargeId);

          if ($charge !== null) {
              $refundApplicationFee = $charge->application_fee_amount > 0;
          }

          $params = [
              'charge' => $chargeId,
              'refund_application_fee' => $refundApplicationFee
          ];
          $this->stripeService->refund($params);
     }

    /**
     * @param string $couponId
     *
     * @return null|Stripe\Coupon
     */
    public function retrieveCoupon($couponId)
    {
        try {
            $coupon = $this->stripeService->retrieveCoupon($couponId);
        } catch (InvalidRequestException $e) {
            $coupon = null;
        }

        return $coupon;
    }

    /**
     * @param Stripe\Coupon $coupon
     *
     * @param int $amount
     * @param int $recurring
     */
    public function applyCoupon(Stripe\Coupon $coupon, &$amount, &$recurring, &$upfrontFee)
    {
        if (!$coupon->valid) {
            return;
        }

        if ($coupon->percent_off) {
            $discount = $coupon->percent_off / 100;
            $amount = $amount - $amount * $discount;

            if ($coupon->duration !== 'once') {
                $recurring = $recurring - $recurring * $discount;
            }
            $upfrontFee = $upfrontFee - $upfrontFee * $discount;

        } else {
            $discount = $coupon->amount_off / 100;
            $amount = $amount - $discount;

            if ($coupon->duration !== 'once') {
                $recurring = $recurring - $discount;
            }
            $upfrontFee = $upfrontFee - $discount;
        }
    }

    public function updateUserStripeConnectAmount(float $amount, string $currency, int $type): StripeConnect
    {
        if ($this->userStripe === null) {
            throw new \RuntimeException();
        }

        $userStripeConnect = (new StripeConnect($this->userStripe->getUser(), $currency))
            ->setAmount($amount)
            ->setType($type);

        $this->em->persist($userStripeConnect);
        $this->em->flush();

        return $userStripeConnect;
    }

    /**
     * @param ClientStripe $clientStripe
     *
     * @return bool
     */
    public function clientHasActiveSubscription(ClientStripe $clientStripe)
    {
        return rescue(function () use ($clientStripe) {
            $subscription = $this->stripeService->retrieveSubscription($clientStripe->getStripeSubscription());
            $clientStripePayment = $clientStripe->getPayment();
            if ($clientStripePayment === null) {
                throw new \RuntimeException();
            }

            return $subscription
              && $subscription->status !== Stripe\Subscription::STATUS_CANCELED
              && $subscription->status !== Stripe\Subscription::STATUS_INCOMPLETE
              && $subscription->status !== Stripe\Subscription::STATUS_INCOMPLETE_EXPIRED
              && $this->customer->id === $clientStripe->getStripeCustomer()
              && $this->payment->getCurrency() === $clientStripePayment->getCurrency();
        }, false);
    }

    /**
     * @param $sessionId
     */
    public function updateClientCard($sessionId)
    {
        $clientStripe = $this->getClientStripeEntity();
        $session = $this
            ->stripeService
            ->retrieveSession($sessionId);

        $setupIntent = $this->stripeService->retrieveIntent($session->setup_intent);
        $this->stripeService->updatePaymentMethod($setupIntent->payment_method, $clientStripe->getStripeCustomer());
    }

    public function createSession()
    {
        $clientStripeEntity = $this->getClientStripeEntity();
        if ($clientStripeEntity === null) {
            throw new \RuntimeException('No ClientStripe entity');
        }
        return $this->stripeService->createSession(
            $clientStripeEntity->getStripeCustomer(),
            $this->appHostname . '/client/settings',
            $this->appHostname . '/client/settings'
        );
    }

    /**
     * @return array
     */
    private function getUserStripeAccount()
    {
        if ($this->userStripe === null) {
            throw new \RuntimeException('No UserStripe specified');
        }

        return ['stripe_account' => $this->userStripe->getStripeUserId()];
    }

    /**
     * @return float
     */
    private function applyApplicationFee()
    {
        if (!$this->getApplicationFeeRequired()) {
            return 0;
        }

        if ($this->payment->getApplicationFee() !== null) {
            return $this->payment->getApplicationFee();
        }

        if ($this->getStripeUserFeePercentage() && $this->getStripeUserFeePercentage() != 0) {
            return $this->getStripeUserFeePercentage();
        }

        return 0;
    }

}
