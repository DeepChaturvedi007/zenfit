<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Repository\StripeConnectRepository;
use AppBundle\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Services\PdfService;
use AppBundle\Services\MailService;

class GenerateStripeReceiptCommand extends CommandBase
{
    private StripeConnectRepository $stripeConnectRepository;
    private UserRepository $userRepository;
    private PdfService $pdfService;
    private MailService $mailService;
    private string $mailerZfEmail;
    private string $mailerZfName;

    public function __construct(
        StripeConnectRepository $stripeConnectRepository,
        UserRepository $userRepository,
        PdfService $pdfService,
        MailService $mailService,
        string $mailerZfEmail,
        string $mailerZfName
    ) {
        $this->stripeConnectRepository = $stripeConnectRepository;
        $this->userRepository = $userRepository;
        $this->pdfService = $pdfService;
        $this->mailService = $mailService;
        $this->mailerZfName = $mailerZfName;
        $this->mailerZfEmail = $mailerZfEmail;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('zf:generate:stripe:receipt')
            ->setDescription('Generate Stripe invoice.')
            ->addArgument('user', InputArgument::REQUIRED, 'user')
            ->addArgument('start', InputArgument::REQUIRED, 'start')
            ->addArgument('end', InputArgument::REQUIRED, 'end');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this
            ->userRepository
            ->find($input->getArgument('user'));

        if ($user === null) {
            throw new BadRequestHttpException('User not found.');
        }

        $start = new \DateTime($input->getArgument('start'));
        $end = new \DateTime($input->getArgument('end'));

        $connectFees = $this
            ->stripeConnectRepository
            ->getConnectFeesBetweenDates($start, $end, $user);

        $result = $this->pdfService->exportReceipt($connectFees, $start, $end, $user);
        $filename = $user->getName();
        $this->sendEmail($result, 'tumsemm@gmail.com', 'Invoice Zenfit', 'Monthly invoice', $filename);

        return 0;
    }

    private function sendEmail(string $attachment, string $to, string $subject, string $content, string $filename): void
    {
        $sgEmail = $this
            ->mailService
            ->createPlainTextEmail(
                $to,
                $subject,
                $this->mailerZfEmail,
                $this->mailerZfName,
                null,
                $content,
                true
            );

        $sgEmail->addAttachment(
            base64_encode($attachment),
            'application/pdf',
            $filename,
            'attachment');

        $this->mailService->send(null, $sgEmail);
    }

}
