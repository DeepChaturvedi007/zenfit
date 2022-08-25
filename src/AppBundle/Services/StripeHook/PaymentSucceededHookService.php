<?php

namespace AppBundle\Services\StripeHook;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSubscription;
use AppBundle\Services\StripeConnectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\StripeConnect;
use AppBundle\Entity\Event;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaymentSucceededHookService
{
    private EntityManagerInterface $em;
    private string $type;
    private string $customer;
    private string $currency;
    private ?string $charge;
    private float $amount;
    private float $applicationFee;
    private string $stripeAccount;

    private StripeConnectService $stripeConnect;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        StripeConnectService $stripeConnect
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->stripeConnect = $stripeConnect;
    }

    public function setStripeAccount(string $acc): self
    {
        $this->stripeAccount = $acc;
        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setCharge(?string $charge): self
    {
        $this->charge = $charge;
        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function setApplicationFee(float $applicationFee): self
    {
        $this->applicationFee = $applicationFee;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function insert()
    {
        if($this->stripeAccount == 'connect') {
            /** @var ?ClientStripe $clientStripe */
            $clientStripe = $this->em
                ->getRepository(ClientStripe::class)
                ->findOneBy(['stripeCustomer' => $this->customer]);

            if($clientStripe === null) {
                return;
            }

            $clientStripe
                ->setLastPaymentFailed(false)
                ->setInvoiceUrl(null)
                ->setPaused(false)
                ->setPausedUntil(null)
                ->setAttemptCount(0)
                ->setPaymentWarningCount(0)
                ->setLastPaymentWarningDate(null);

            $paymentsLog = (new PaymentsLog($this->type))
                ->setCustomer($this->customer)
                ->setClient($clientStripe->getClient())
                ->setAmount($this->amount)
                ->setCharge($this->charge)
                ->setCurrency($this->currency);

            $this->em->persist($paymentsLog);
            $client = $clientStripe->getClient();
            //open client's app if closed
            $client->setAccessApp(true);

            $user = $client->getUser();

            //application fee stuff
            $userStripe = $user->getUserStripe();
            if ($userStripe === null) {
                throw new \RuntimeException('No UserStripe');
            }

            //log fee in DB
            if ($this->applicationFee > 0) {
                $userStripeConnect = $this
                    ->stripeConnect
                    ->setUserStripe($userStripe)
                    ->updateUserStripeConnectAmount($this->applicationFee, $this->currency, StripeConnect::TYPE_FEE);

                $paymentsLog->setStripeConnect($userStripeConnect);
            }

            $this->em->flush();

            // dispatch payment succeeded event
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($clientStripe->getClient(), Event::PAYMENT_SUCCEEDED);
            $dispatcher->dispatch($event, Event::PAYMENT_SUCCEEDED);
        } else {
            $repo = $this->em->getRepository(UserSubscription::class);
            $userSubscription = $repo->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if(!$userSubscription) {
              return;
            }

            /** @var UserSubscription $userSubscription */
            $userSubscription
              ->setLastPaymentFailed(false)
              ->setNextPaymentAttempt(null)
              ->setAttemptCount(0)
              ->setInvoiceUrl(null);

            /** @var User $user */
            $user = $userSubscription->getUser();
            $user->setActivated(true);
            $this->em->flush();
        }
    }
}
