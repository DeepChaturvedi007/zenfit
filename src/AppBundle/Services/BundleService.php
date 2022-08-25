<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\Bundle;
use AppBundle\Entity\BundleLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BundleService
{
    private EntityManagerInterface $em;
    private QueueService $queueService;
    private UrlGeneratorInterface $urlGenerator;
    private string $appHostname;

    public function __construct(
        EntityManagerInterface $em,
        QueueService $queueService,
        UrlGeneratorInterface $urlGenerator,
        string $appHostname
    ) {
        $this->em = $em;
        $this->queueService = $queueService;
        $this->appHostname = $appHostname;
        $this->urlGenerator = $urlGenerator;
    }

    public function addBundleLog($key, Bundle $bundle, Client $client = null)
    {
        $bundleLog = new BundleLog($bundle, $key);
        $bundleLog
          ->setPurchaseDate(new \DateTime())
          ->setClient($client);

        $this->em->persist($bundleLog);
        $this->em->flush();

        return $bundleLog;
    }

    public function sendPlansEmailToClient($company, $link, Client $client)
    {
        $message = "Thanks for your purchase of $company's plans!<br /><br />In the following link, you will find your plans: $link";
        $queueService = $this->queueService;
        $queueService->sendEmailToClient(
            $message,
            'Thank You - Here Are Your Plans!',
            $client->getEmail(),
            $client->getName(),
            $client
        );
    }

    public function sendEmailToTrainerOfPurchase(Client $client)
    {
        $url = $this->appHostname . $this->urlGenerator->generate('plansOverview');

        $message = "Someone bought one of your plans!<br /><br />
            Name: {$client->getName()}<br />
            Email: {$client->getEmail()}<br />
            Click <a href=$url>here</a> to check it out!";

        $this->queueService->sendEmailToTrainer(
            $message,
            'You got a new paying customer!',
            $client->getUser()->getEmail(),
            $client->getUser()->getEmailName()
        );
    }
}
