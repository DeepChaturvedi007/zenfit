<?php

namespace AppBundle\Services\StripeHook;

use AppBundle\Entity\User;
use AppBundle\Entity\UserSubscription;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Services\MailService;
use AppBundle\Entity\Event;
use AppBundle\Entity\ClientStripe;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentFailedHookService
{
    private EntityManagerInterface $em;
    private EventDispatcherInterface $eventDispatcher;
    private MailService $mailService;
    private TranslatorInterface $translator;
    private string $mailerZfBillingEmail;
    private string $mailerZfName;
    private $type;
    private $customer;
    private $currency;
    private $amount;
    private $attemptCount;
    private $nextPaymentAttempt;
    private $invoiceUrl;
    private $stripeAccount;
    private $accountCountry;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        MailService $mailService,
        TranslatorInterface $translator,
        string $mailerZfBillingEmail,
        string $mailerZfName
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailService = $mailService;
        $this->translator = $translator;
        $this->mailerZfBillingEmail = $mailerZfBillingEmail;
        $this->mailerZfName = $mailerZfName;
    }

    public function setStripeAccount($acc)
    {
        $this->stripeAccount = $acc;
        return $this;
    }

    public function setAccountCountry($country)
    {
        $this->accountCountry = $country;
        return $this;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setAttemptCount($attemptCount)
    {
        $this->attemptCount = $attemptCount;
        return $this;
    }

    public function setNextPaymentAttempt($nextPaymentAttempt)
    {
        $this->nextPaymentAttempt = $nextPaymentAttempt;
        return $this;
    }

    public function setInvoiceUrl($invoiceUrl)
    {
        $this->invoiceUrl = $invoiceUrl;
        return $this;
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
              ->setLastPaymentFailed(true)
              ->setNextPaymentAttempt($this->nextPaymentAttempt)
              ->setAttemptCount($this->attemptCount)
              ->setInvoiceUrl($this->invoiceUrl);

            $paymentsLog = new PaymentsLog($this->type);
            $paymentsLog
                ->setCustomer($this->customer)
                ->setClient($clientStripe->getClient())
                ->setAmount($this->amount)
                ->setCurrency($this->currency);

            $this->em->persist($paymentsLog);
            $this->em->flush();

            // dispatch payment failed event
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($clientStripe->getClient(), Event::PAYMENT_FAILED);
            $dispatcher->dispatch($event, Event::PAYMENT_FAILED);

            //send email to client if its the first attempt
            /*if ($this->attemptCount == 1) {
                $this->prepareEmailToClient($clientStripe);
            }*/
        } else {
            $repo = $this->em->getRepository(UserSubscription::class);
            /** @var UserSubscription $userSubscription */
            $userSubscription = $repo->findOneBy([
                'stripeCustomer' => $this->customer
            ]);

            if(!$userSubscription) {
              return;
            }

            $userSubscription
              ->setLastPaymentFailed(true)
              ->setNextPaymentAttempt($this->nextPaymentAttempt)
              ->setAttemptCount($this->attemptCount)
              ->setInvoiceUrl($this->invoiceUrl);

            if($this->attemptCount > 1) {
                /** @var User $user */
                $user = $userSubscription->getUser();
                $user->setActivated(false);
            }

            $this->em->flush();
        }
    }

    private function prepareEmailToClient(ClientStripe $clientStripe)
    {
        $invoiceUrl = $this->invoiceUrl;
        $amount = $this->amount;
        $currency = $this->currency;
        $client = $clientStripe->getClient();
        $trainer = $client->getUser()->getTrainerName();
        $locale = $client->getLocale();

        //we only do this for DK + SE right now
        $allowedCountries = ['DK', 'SE'];
        if (!in_array($this->accountCountry, $allowedCountries)) {
            return;
        }

        if ($this->accountCountry == 'DK') {
            $this->translator->setLocale('da_DK');
            //set client's locale to this lang
            $client->setLocale('da_DK');
        } elseif ($this->accountCountry == 'SE') {
            $this->translator->setLocale('sv_SE');
            //set client's locale to this lang
            $client->setLocale('sv_SE');
        }

        $subject = $this->translator->trans('emails.client.paymentFailed1.subject', [
            '%trainer%' => $trainer
        ]);

        $msg = $this->translator->trans('emails.client.paymentFailed1.body', [
            '%name%' => $client->getName(),
            '%trainer%' => $trainer,
            '%amount%' => $amount,
            '%currency%' => strtoupper($currency),
            '%invoiceUrl%' => "<a href={$invoiceUrl}>{$this->translator->trans('emails.client.paymentFailed1.cta')}</a>"
        ]);

        $email = $this
            ->mailService
            ->createPlainTextEmail(
                $client->getEmail(),
                $subject,
                $this->mailerZfBillingEmail,
                $this->mailerZfName,
                [],
                $msg,
                true
            );

        $this->mailService->send(null, $email);
        $clientStripe
            ->setLastPaymentWarningDate(new \DateTime('now'))
            ->setPaymentWarningCount(1);
        $this->em->flush();
    }

}
