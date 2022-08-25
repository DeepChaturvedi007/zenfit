<?php

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Services\MailService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Entity\Queue;

class SendClientEmailFromTrainerCommand extends CommandBase
{
    public function __construct(
        private MailService $mailService,
        private string $mailerZfEmail,
        private LoggerInterface $logger,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:email:from:trainer:send')
            ->setDescription('Send email to client from trainer')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = $this->mailService;
        $recipients = $service->getClientsThatShouldReceiveEmailFromTrainer();
        $zfMailerEmail = $this->mailerZfEmail;

        foreach($recipients as $recipient) {
            try {
                /** @var $recipient Queue */
                $user = $recipient->getUser();
                if ($user) {
                    if ($user->getDeleted()) { continue;}
                }

                $msg = $recipient->getMessage();
                $trainer = $recipient->getClient()->getUser()->getTrainerName();
                $fromName = $recipient->getClient()->getUser()->getUserApp() ? $recipient->getClient()->getUser()->getUserApp()->getTitle() : $trainer;

                $subject = $recipient->getSubject() ? $recipient->getSubject() : $trainer . ' Personal Training';

                $email = $service->createPlainTextEmail(
                    $recipient->getEmail(),
                    $subject,
                    $zfMailerEmail,
                    $fromName,
                    $recipient->getId(),
                    $msg,
                    true
                );

                $service->send($recipient, $email);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $recipient->setStatus(Queue::STATUS_ERROR);
                $this->em->flush();
                continue;
            }
        }

        return 0;
    }
}
