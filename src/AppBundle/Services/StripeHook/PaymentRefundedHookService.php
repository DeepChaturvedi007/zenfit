<?php

namespace AppBundle\Services\StripeHook;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Repository\ClientStripeRepository;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\StripeConnect;
use AppBundle\Services\StripeConnectService;

class PaymentRefundedHookService
{
    private StripeConnectService $stripeConnect;
    private EntityManagerInterface $em;
    private ClientStripeRepository $clientStripeRepository;
    private string $type;
    private string $customer;
    private string $currency;
    private float $amount;
    private float $applicationFee;

    public function __construct(
        EntityManagerInterface $em,
        ClientStripeRepository $clientStripeRepository,
        StripeConnectService $stripeConnect
    ) {
        $this->clientStripeRepository = $clientStripeRepository;
        $this->em = $em;
        $this->stripeConnect = $stripeConnect;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setApplicationFee(float $applicationFee): self
    {
        $this->applicationFee = $applicationFee;
        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function insert(): void
    {
        $clientStripe = $this
            ->clientStripeRepository
            ->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

        if($clientStripe !== null) {

            $paymentsLog = new PaymentsLog($this->type);
            $paymentsLog
                ->setCustomer($this->customer)
                ->setClient($clientStripe->getClient())
                ->setAmount($this->amount)
                ->setCurrency($this->currency);

            $this->em->persist($paymentsLog);

            if ($this->applicationFee > 0) {
                $user = $clientStripe->getClient()->getUser();
                $userStripe = $user->getUserStripe();

                if ($userStripe !== null) {
                    $userStripeConnect = $this
                        ->stripeConnect
                        ->setUserStripe($userStripe)
                        ->updateUserStripeConnectAmount($this->applicationFee, $this->currency, StripeConnect::TYPE_REFUND);

                    $paymentsLog->setStripeConnect($userStripeConnect);
                }
            }

            $this->em->flush();
        }
    }

}
