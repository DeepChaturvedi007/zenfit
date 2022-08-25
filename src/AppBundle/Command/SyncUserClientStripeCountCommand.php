<?php declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Entity\ClientStatus;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Services\StripeService;

class SyncUserClientStripeCountCommand extends CommandBase
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
            ->setName('zf:sync:user:client:stripe:count')
            ->setDescription('Sync stripe with latest user client count.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;

        $clientsNeedWelcome = $em->getRepository(ClientStatus::class)
            ->createQueryBuilder('cs')
            ->select('IDENTITY(cs.client) as id')
            ->innerJoin('cs.client', 'c')
            ->andWhere('c.active=1 and c.deleted = 0')
            ->andWhere('cs.resolved = 0')
            ->andWhere('cs.event = :event')
            ->setParameter('event', $em->getRepository(Event::class)->findOneBy(['name' => Event::NEED_WELCOME]))
            ->groupBy('cs.client')
            ->getQuery()
            ->getResult();

        $clientsNeedWelcome = array_map(static fn ($item) => $item['id'], $clientsNeedWelcome);

        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('us.stripeSubscription as stripe_subscription, u.name as name, count(c.id) as clients_count')
            ->join('u.userSubscription', 'us')
            ->groupBy('us.stripeSubscription, u.name')
            ->leftJoin('u.clients', 'c', 'WITH', 'c.active=1 and c.deleted=0 and c.id not in (:clientsNeedWelcome)')
            ->setParameter('clientsNeedWelcome', $clientsNeedWelcome)
            ->join('us.subscription','s')
            ->andWhere('s.tiered = 1')
            ->andWhere('us.stripeSubscription IS NOT NULL')
            ->orderBy('us.stripeSubscription')
            ->getQuery()
            ->getResult();

        foreach($users as $user) {
            try {
                $subscription = $this->stripeService->retrieveSubscription($user['stripe_subscription']);
                $currentPeriodEnd = $subscription->current_period_end;
                $currentPeriodEndDT = new \DateTime();
                $currentPeriodEndDT->setTimestamp($currentPeriodEnd);
                $today = new \DateTime('now');

                $days = $currentPeriodEndDT->diff($today)->format('%a');
                $totalActiveClients = $user['clients_count'];
                if($days < 1 && $subscription->quantity != $totalActiveClients) {
                    $subscription->quantity = $totalActiveClients;
                    $subscription->save();

                    $this->stripeService->deleteInvoiceItems($subscription->customer);
                    echo $user['name'];
                    echo 'Subscription updated.';
                }

            } catch (\Exception $e) {
                //var_dump($e->getMessage());
            }
        }

        return 0;
    }

}
