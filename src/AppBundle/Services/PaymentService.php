<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Stripe;
use Carbon\Carbon;

class PaymentService
{
    protected EntityManagerInterface $em;
    protected QueueService $queueService;

    public function __construct(EntityManagerInterface $em, QueueService $queueService)
    {
        $this->em = $em;
        $this->queueService = $queueService;
    }

    /**
     * @param int $signUpFee
     * @param int $monthlyAmount
     * @param string $periods
     */
    public function validatePaymentInput($signUpFee, $monthlyAmount, $periods, ?int $startPaymentTs): void
    {
        if ($signUpFee && !filter_var($signUpFee, FILTER_VALIDATE_INT)) {
            throw new HttpException(422, 'Invalid upfront fee.');
        }

        if ($monthlyAmount && !filter_var($monthlyAmount, FILTER_VALIDATE_INT)) {
            throw new HttpException(422, 'Invalid recurring fee.');
        }

        if ((!$periods||$periods == ''||$periods == "-1"||$periods == -1||$periods == 'null'||$periods == null) && $periods != "0") {
            throw new HttpException(422, 'Invalid number of months.');
        }

        if (!$monthlyAmount && !$signUpFee) {
            throw new HttpException(422, 'You need to put either an upfront fee / recurring fee or both.');
        }

        if ($periods && !$monthlyAmount) {
            throw new HttpException(422, 'You need to set a monthly recurring amount if you define the number of months.');
        }

        if ($startPaymentTs && $startPaymentTs < Carbon::now()->timestamp) {
            throw new HttpException(422, 'You cannot declare a start date in the past.');
        }

    }

    public function generatePayment(
        Client $client,
        string $currency,
        float $upfrontFee,
        float $recurringFee,
        int $months,
        ?int $startPaymentTs = null,
        ?string $terms = null,
        bool $delayUpfront = false,
        ?float $applicationFee = null,
        ?User $salesPerson = null
    ): Payment
    {
        $upfrontFee = round($upfrontFee, 1);
        $recurringFee = round($recurringFee, 1);

        $key = $this
            ->queueService
            ->getRandomKey();

        $payment = (new Payment($client, $key))
            ->setMonths($months)
            ->setCurrency($currency)
            ->setCharged(false)
            ->setUpfrontFee($upfrontFee)
            ->setSentAt(new \DateTime())
            ->setRecurringFee($recurringFee)
            ->setTrialEnd((string) $startPaymentTs)
            ->setTerms($terms)
            ->setDelayUpfront($delayUpfront)
            ->setApplicationFee($applicationFee)
            ->setSalesPerson($salesPerson)
        ;

        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }
}
