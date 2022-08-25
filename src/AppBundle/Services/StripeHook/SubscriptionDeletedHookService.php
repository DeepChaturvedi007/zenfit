<?php

namespace AppBundle\Services\StripeHook;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\Event;
use AppBundle\Entity\UserSubscription;
use AppBundle\Event\ClientMadeChangesEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SubscriptionDeletedHookService
{
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;
    private $currentPeriodEnd;
    private $customer;
    private $type;
    private $canceled;
    private $canceledAt;
    private $stripeAccount;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setStripeAccount($acc)
    {
        $this->stripeAccount = $acc;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setCurrentPeriodEnd($currentPeriodEnd)
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
        return $this;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;
        return $this;
    }

    public function setCanceledAt($canceledAt)
    {
        $this->canceledAt = $canceledAt;
        return $this;
    }

    public function insert()
    {
        if($this->stripeAccount == 'connect') {
            $repo = $this->em->getRepository(ClientStripe::class);
            $clientStripe = $repo->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if(!$clientStripe) {
              return;
            }

            $clientStripe
              ->setCanceled($this->canceled)
              ->setCanceledAt($this->canceledAt);

            $paymentsLog = new PaymentsLog($this->type);
            $paymentsLog
                ->setCustomer($this->customer)
                ->setClient($clientStripe->getClient());
            $this->em->persist($paymentsLog);
            $this->em->flush();

            // dispatch subscription canceled event
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($clientStripe->getClient(), Event::SUBSCRIPTION_CANCELED);
            $dispatcher->dispatch($event, Event::SUBSCRIPTION_CANCELED);
        } else {
            $repo = $this->em->getRepository(UserSubscription::class);
            $userSubscription = $repo->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if ($userSubscription === null) {
              return;
            }

            $userSubscription
                ->setCanceled($this->canceled)
                ->setCanceledAt($this->canceledAt);

            $userSubscription
                ->getUser()
                ->setActivated(false);

            $this->em->flush();
        }

    }
}
