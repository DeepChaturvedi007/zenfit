<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Str;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QueueService
{
    protected EntityManagerInterface $em;
    private UrlGeneratorInterface $urlGenerator;
    private string $appHostname;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, string $appHostname)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->appHostname = $appHostname;
    }

    /**
     * @param string $email
     * @param string $name
     * @param int $status
     * @param int $type
     * @param Client $client
     * @param string $key
     * @param Payment $payment
     * @param string $subject
     * @param array|null $parameters
     * @param User $user
     * @param string $message
     *
     * @return Queue
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function insertIntoEmailQueue($email, $name, $status, $type, Client $client = null, $key = null, Payment $payment = null, $subject = null, $parameters = null, User $user = null, $message = null)
    {
        $queue = (new Queue($email, $name, $status, $type))
            ->setDatakey($key)
            ->setClient($client)
            ->setUser($user)
            ->setPayment($payment)
            ->setSubject($subject)
            ->setMessage($message);

        $this->em->persist($queue);
        $this->em->flush();

        return $queue;
    }

    /**
     * @return string
     */
    public function getRandomKey()
    {
        return Str::random(32);
    }

    /**
     * @param Client $client
     * @param int $status
     *
     * @return Queue
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createClientCreationEmailQueueEntity(Client $client, $status = Queue::STATUS_TEMPORARY)
    {
        $datakey = $this->getRandomKey();

        return $this->insertIntoEmailQueue(
            $client->getEmail(),
            $client->getName(),
            $status,
            Queue::TYPE_CLIENT_EMAIL,
            $client,
            $datakey
        );
    }

    /**
     * @param string $message
     * @param string $subject
     * @param string $to
     * @param string $name
     *
     * @return Queue
     */
    public function sendEmailToTrainer($message, $subject, $to, $name)
    {
        return $this->insertIntoEmailQueue(
            $to,
            $name,
            Queue::STATUS_PENDING,
            Queue::TYPE_MESSAGE_TO_TRAINER,
            null,
            null,
            null,
            $subject,
            null,
            null,
            $message
        );
    }

    /**
     * @param string $message
     * @param string $subject
     * @param string $to
     * @param string $name
     *
     * @return Queue
     */
    public function sendEmailToClient($message, $subject, $to, $name, $client)
    {
        return $this->insertIntoEmailQueue(
            $to,
            $name,
            Queue::STATUS_PENDING,
            Queue::TYPE_CLIENT_EMAIL,
            $client,
            null,
            null,
            $subject,
            null,
            null,
            $message
        );
    }

    /**
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    public function getAbsoluteUrl($route, array $params = [])
    {
        return $this->appHostname . $this->urlGenerator->generate($route, $params);
    }
}
