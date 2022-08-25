<?php

namespace AppBundle\Command\Trainer;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Queue;
use AppBundle\Services\MailService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTrainerEmailCommand extends CommandBase
{
    public function __construct(private MailService $mailService, private string $mailerZfEmail, private string $mailerZfName)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:trainer:email')
            ->setDescription('Send email to trainer');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mailService = $this->mailService;

        $recipients = $mailService->getTrainersThatShouldReceiveEmail();

        $zfMailerEmail = $this->mailerZfEmail;
        $zfMailerName = $this->mailerZfName;

        foreach ($recipients as $recipient) {
            /** @var $recipient Queue */
            $user = $recipient->getUser();

            if ($user && $user->getDeleted()) {
                continue;
            }

            $message = $recipient->getMessage();

            $email = $mailService->createPlainTextEmail(
                $recipient->getEmail(),
                $recipient->getSubject(),
                $zfMailerEmail,
                $zfMailerName,
                $recipient->getId(),
                $message
            );

            $mailService->send($recipient, $email);
        }

        return 0;
    }
}
