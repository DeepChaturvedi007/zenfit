<?php declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Entity\ClientStripe;
use AppBundle\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Subscription;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionCancelCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private StripeService $stripeService;

    public function __construct(EntityManagerInterface $em, StripeService $stripeService)
    {
        $this->em = $em;
        $this->stripeService = $stripeService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:subscriptions:cancel')
            ->setDescription('Cancel subscriptions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $timestamp = strtotime('+7 days', time());

        $clientStripe = $em->getRepository(ClientStripe::class)
            ->createQueryBuilder('cs')
            ->andWhere('cs.periodEnd<=:time')
            ->andWhere('cs.canceled=0')
            ->setParameter('time', $timestamp)
            ->getQuery()
            ->getResult();

        $output->writeln((string) time());
        $service = $this->stripeService;
        $subscriptionsCanceled = [];

        foreach ($clientStripe as $csi) {
           $payment = $csi->getPayment();

           if($payment) {

               if ($payment->getMonths() == 13) {continue;}
               $userStripe = $csi->getClient()->getUser()->getUserStripe();

               if ($userStripe) {
                 $service->setOptions(['stripe_account' => $userStripe->getStripeUserId()]);
                  try {
                      $subscription = $service->retrieveSubscription($csi->getStripeSubscription());
                      if ($subscription instanceof Subscription && $subscription->status !== 'canceled') {

                          $subscription->cancel();
                          $subscriptionsCanceled[$csi->getStripeSubscription()] = time();
                      }
                  } catch (\Exception $e) {
                      var_dump($e->getMessage());
                  }

               } //end userstripe

           } //end payment

        } //end foreach
        $repo = $em->getRepository(ClientStripe::class);
        foreach ($subscriptionsCanceled as $key=>$sci) {
            $qb = $repo->createQueryBuilder('cs');
            $qb ->andWhere('cs.stripeSubscription=:subscription')
                ->set('cs.canceled', ':canceled')
                ->set('cs.canceledAt', ':canceledAt')
                ->setParameter('canceled', true)
                ->setParameter('canceledAt', $sci)
                ->setParameter('subscription', $key)
                ->update()
                ->getQuery()
                ->execute();
        }
        $output->writeln('Canceled subscriptions:'. count($subscriptionsCanceled));

        return 0;
    }
}
