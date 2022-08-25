<?php

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Services\MailService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Entity\ClientStripe;
use AppBundle\Services\StripeService;

class SendClientPaymentWarningEmailCommand extends CommandBase
{
    private MailService $mailService;
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private StripeService $stripeService;
    private string $mailerZfBillingEmail;
    private string $mailerZfName;

    public function __construct(
        MailService $mailService,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        StripeService $stripeService,
        string $mailerZfBillingEmail,
        string $mailerZfName
    ) {
        $this->mailService = $mailService;
        $this->logger = $logger;
        $this->em = $em;
        $this->translator = $translator;
        $this->stripeService = $stripeService;
        $this->mailerZfBillingEmail = $mailerZfBillingEmail;
        $this->mailerZfName = $mailerZfName;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:payment:warning:email')
            ->setDescription('Send payment warning to client.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //check if we have sent one or more payment warning emails already
        $entries = $this
            ->em
            ->getRepository(ClientStripe::class)
            ->createQueryBuilder('cs')
            ->where('cs.paymentWarningCount > 0')
            ->getQuery()
            ->getResult();

        foreach ($entries as $entry) {
            //we only support DK + SE for now
            $this->translator->setLocale($entry->getClient()->getLocale());

            $paymentWarningCount = $entry->getPaymentWarningCount();
            $lastWarningSent = $entry->getLastPaymentWarningDate();

            //first warning is sent
            //second one should be sent 7 days after
            $sevenDaysAgo = new \DateTime('-7 days');
            if ($paymentWarningCount == 1 && $sevenDaysAgo > $lastWarningSent) {
                //add 100 kr to the invoice as fee for paying late
                $this->createStripeInvoice($entry, $paymentWarningCount);
                $this->sendWarningEmail('emails.client.paymentFailed2', $entry);
            }
            //second warning is sent
            //third one should be sent 10 days after
            $tenDaysAgo = new \DateTime('-10 days');
            if ($paymentWarningCount == 2 && $tenDaysAgo > $lastWarningSent) {
                //add 100 kr to the invoice as fee for paying late
                $this->createStripeInvoice($entry, $paymentWarningCount);
                $this->sendWarningEmail('emails.client.paymentFailed3', $entry);
                //disable client's access to the app
                $entry->getClient()->getAccessApp(false);
                $this->em->flush();
            }
        }

        return 0;
    }

    private function sendWarningEmail(string $translationKey, ClientStripe $clientStripe)
    {
        $client = $clientStripe->getClient();
        $trainer = $client->getUser()->getTrainerName();
        $amount = $clientStripe->getPayment()->getRecurringFee();
        $currency = $clientStripe->getPayment()->getCurrency();
        $invoiceUrl = $clientStripe->getInvoiceUrl();

        $subject = $this->translator->trans($translationKey.'.subject', [
            '%trainer%' => $trainer
        ]);

        $msg = $this->translator->trans($translationKey.'.body', [
            '%name%' => $client->getName(),
            '%trainer%' => $trainer,
            '%amount%' => $amount,
            '%currency%' => strtoupper($currency),
            '%invoiceUrl%' => "<a href={$invoiceUrl}>{$this->translator->trans($translationKey.'.cta')}</a>"
        ]);

        $to = $clientStripe->getClient()->getEmail();

        $email = $this
            ->mailService
            ->createPlainTextEmail(
                $to,
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
            ->setPaymentWarningCount($clientStripe->getPaymentWarningCount()+1);
        $this->em->flush();
    }

    private function createStripeInvoice(ClientStripe $clientStripe, $count)
    {
        $userStripe = $clientStripe->getClient()->getUser()->getUserStripe();
        if ($userStripe !== null) {
            $this->stripeService->setOptions(['stripe_account' => $userStripe->getStripeUserId()]);
        }

        //get the existing invoices
        $existingInvoices = $this->stripeService->getInvoices($clientStripe->getStripeCustomer(), 'open');

        if (count($existingInvoices->data) === 0) {
            throw new BadRequestHttpException('No open invoices');
        }

        //get currency and amount
        //and then cancel it
        $existingInvoice = $existingInvoices->data[0];

        //add 100 kr to the invoice (100 kr * 100)
        $zenfitFee = 100;
        $amount = ($existingInvoice->amount_due / 100) + $zenfitFee; /** @phpstan-ignore-line */
        $currency = $existingInvoice->currency; /** @phpstan-ignore-line */
        $existingInvoice->voidInvoice(); /** @phpstan-ignore-line */

        $customer = $this->stripeService->retrieveCustomer($clientStripe->getStripeCustomer());
        //create a new invoice with 100 kr. fee
        $trainer = $clientStripe->getClient()->getUser()->getTrainerName();
        $this->stripeService->createInvoiceItem(
            $amount,
            $currency,
            $customer,
            $this->translator->trans('emails.client.paymentFailed2.subject', ['%trainer%' => $trainer])
        );

        $invoiceParams = [
            'customer' => $clientStripe->getStripeCustomer(),
            'auto_advance' => true,
            'collection_method' => 'send_invoice',
            'days_until_due' => 3,
            'application_fee_amount' => $zenfitFee * 100 * $count
        ];

        $invoice = $this->stripeService->createInvoice($invoiceParams);
        $invoice->sendInvoice();
        $invoiceUrl = $invoice->hosted_invoice_url;
        $clientStripe->setInvoiceUrl($invoiceUrl);
        $this->em->flush();
    }
}
