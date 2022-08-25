<?php

namespace AppBundle\Services\StripeHook;

use AppBundle\Entity\UserStripe;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\PaymentsLog;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayoutHookService
{
    private EntityManagerInterface $em;
    private $type;
    private $currency;
    private $amount;
    private $arrivalDate;
    private $stripeAccount;
    private $userStripeAccount;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function setStripeAccount($acc)
    {
        $this->stripeAccount = $acc;

        return $this;
    }

    public function setUserStripeAccount($userStripeAccount)
    {
        $this->userStripeAccount = $userStripeAccount;

        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function setArrivalDate($arrivalDate)
    {
        $date = new \DateTime();
        $date->setTimestamp($arrivalDate);
        $dateTime = $date->format('Y-m-d H:i:s');
        $this->arrivalDate = new \DateTime($dateTime);

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function insert()
    {
        if ($this->stripeAccount == 'connect') {
            $userStripe = $this
                ->em
                ->getRepository(UserStripe::class)
                ->findOneBy([
                    'stripeUserId' => $this->userStripeAccount
                ]);

            if ($userStripe === null) {
                throw new NotFoundHttpException('UserStripe not found');
            }

            $paymentsLog = new PaymentsLog($this->type);
            $paymentsLog
                ->setAmount($this->amount)
                ->setCurrency($this->currency)
                ->setArrivalDate($this->arrivalDate)
                ->setUser($userStripe->getUser());

            $this->em->persist($paymentsLog);
            $this->em->flush();
        }
    }

}
