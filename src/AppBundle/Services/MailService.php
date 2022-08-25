<?php

namespace AppBundle\Services;

use AppBundle\Entity\Queue;
use Doctrine\ORM\EntityManagerInterface;
use SendGrid;

class MailService
{
    protected EntityManagerInterface $em;

    protected $db;
    private string $sendgridApiKey;

    public function __construct(EntityManagerInterface $em, string $sendgridApiKey)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
        $this->sendgridApiKey = $sendgridApiKey;
    }

    public function getClientsThatShouldReceiveEmailFromTrainer()
    {
        $em = $this->em;
        $recipients = $em
            ->getRepository(Queue::class)
            ->createQueryBuilder('q')
            ->where('q.status = :pending')
            ->andWhere('q.type = :welcome')
            ->setParameters(array(
              'pending' => Queue::STATUS_PENDING,
              'welcome' => Queue::TYPE_CLIENT_EMAIL
            ))
            ->getQuery()
            ->getResult();

        return $recipients;
    }

    /** @return array<Queue> */
    public function getTrainersThatShouldReceiveEmail(): array
    {
        return $this->em
            ->getRepository(Queue::class)
            ->createQueryBuilder('q')
            ->where('q.status = :status')
            ->andWhere('q.type = :type')
            ->setParameters([
                'status' => Queue::STATUS_PENDING,
                'type' => Queue::TYPE_MESSAGE_TO_TRAINER,
            ])
            ->getQuery()
            ->getResult();
    }

    public function getClientsThatShouldHaveMessageNotificationSent()
    {
        $em = $this->em;
        $recipients = $em->getRepository(Queue::class)->findBy([
            'status' => 0,
            'type' => Queue::TYPE_CLIENT_MESSAGE_NOTIFICATION
        ]);

        return $recipients;
    }

    public function createPlainTextEmail(
        $to,
        $subject,
        $from,
        $fromName,
        $queueId,
        $content,
        $bypassUnsubscriptions = false
    ): SendGrid\Mail\Mail {
        $email = new SendGrid\Mail\Mail();
        $email->addTo($to);
        $email->setFrom($from, $fromName);
        $email->setSubject($subject);
        $email->addContent('text/html', $content);
        $email->addContent('text/plain', strip_tags($content));

        if ($bypassUnsubscriptions) {
            $email->enableBypassListManagement();
        }

        $customArgs = [];

        if (!empty($queueId)) {
            $customArgs['queue'] = (string) $queueId;
        }

        $email->addCustomArgs($customArgs);

        return $email;
    }

    /**
     * @param string $to
     * @param string $templateId
     * @param string $subject
     * @param array $parameters
     * @param string $from
     * @param string $fromName
     * @param * $queueId
     * @param * $emailLogId
     * @return SendGrid\Mail\Mail
     * @throws SendGrid\Mail\TypeException
     */
    public function createMailWithTemplate(
        $to,
        $templateId,
        $subject = 'Zenfit',
        $parameters = [],
        $from = 'no-reply@zenfitapp.com',
        $fromName = 'Zenfit',
        $queueId = null,
        $emailLogId = null)
    {
        $email = new SendGrid\Mail\Mail();
        $email->addTo($to);
        $email->setFrom($from, $fromName);
        $email->setSubject($subject);
        $email->setTemplateId($templateId);
        $email->addContent('text/html', '<p></p>');

        $customArgs = [];

        if (!empty($queueId)) {
            $customArgs['queue'] = (string) $queueId;
        }

        $email->addCustomArgs($customArgs);

        foreach ($parameters as $key => $parameter) {
            if (!$parameter) {
                continue;
            }
            if ($key == '-messagefromtrainer-') {
              $email->addContent('text/plain', strip_tags($parameter));
            }
            $email->addSubstitution($key, $parameter);
        }

        return $email;
    }

    /**
     * @param Queue|null $queue
     * @param SendGrid\Mail\Mail $email
     * @param callable|null $successCallback
     * @param callable|null $errorCallback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send(?Queue $queue, SendGrid\Mail\Mail $email, callable $successCallback = null, callable $errorCallback = null)
    {
        $messenger = new SendGrid($this->sendgridApiKey);

        try {
            $messenger->send($email);

            if ($queue) {
                $queue->setStatus(Queue::STATUS_SENT);
            }

            if ($successCallback) {
                $successCallback();
            }
        } catch (\Exception $e) {
            if ($queue) {
                $queue->setStatus(Queue::STATUS_ERROR);
            }

            if ($errorCallback) {
                $errorCallback($e);
            }
        }

        if ($queue) {
            $this->em->flush();
        }
    }

    /**
     * @param $dateTimeNow
     * @param $daysBackOrForward
     * @return false|string
     */
    public function dateDaysAgoOrInFuture($dateTimeNow, $daysBackOrForward)
    {
        return date('Y-m-d', strtotime($daysBackOrForward, strtotime($dateTimeNow)));
    }
}
