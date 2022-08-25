<?php

namespace AppBundle\Services\StripeHook;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\UserSubscription;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\PaymentsLog;

class SubscriptionCreatedHookService
{
    private EntityManagerInterface $em;
    private $type;
    private $customer;
    private $stripeAccount;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setStripeAccount($acc)
    {
        $this->stripeAccount = $acc;
        return $this;
    }

    public function insert()
    {
        if($this->stripeAccount == 'connect') {
            $clientStripe = $this->em->getRepository(ClientStripe::class)->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if($clientStripe) {
                $paymentsLog = new PaymentsLog($this->type);
                $paymentsLog
                    ->setCustomer($this->customer)
                    ->setClient($clientStripe->getClient());

                $this->em->persist($paymentsLog);
                $this->em->flush();
            }
        } else {
            $repo = $this->em->getRepository(UserSubscription::class);
            $userSubscription = $repo->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if(!$userSubscription) {
              return;
            }

            $userSubscription
                ->getUser()
                ->setActivated(true);

            $this->em->flush();
        }

    }

}
