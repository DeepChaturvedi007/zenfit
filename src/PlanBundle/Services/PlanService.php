<?php

namespace PlanBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\Plan;
use AppBundle\Entity\Bundle;
use AppBundle\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;

class PlanService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
    * @param Client $client
    * @param int $type
    * @param String $title
    * @param Bundle $bundle
    * @param Payment $payment

    * @return Plan
    */
    public function createPlan(Client $client, $type, $title, Bundle $bundle = null, Payment $payment = null)
    {
        $plan = $this
            ->em
            ->getRepository(Plan::class)
            ->findOneBy([
                'client' => $client
            ]);

        if (!$plan) {
            $plan = new Plan();
            $plan
                ->setClient($client)
                ->setType($type)
                ->setTitle($title)
                ->setBundle($bundle)
                ->setPayment($payment)
                ->setCreatedAt(new \DateTime());

            $this->em->persist($plan);
            $this->em->flush();
        }

        return $plan;
    }
}
