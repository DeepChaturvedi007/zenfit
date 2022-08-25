<?php

namespace AdminBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Repository\LeadRepository;
use GymBundle\Repository\GymRepository;
use AppBundle\Repository\UserSubscriptionRepository;
use AppBundle\Repository\StripeConnectRepository;
use AppBundle\Entity\Lead;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\UserSubscription;

class AdminService
{
    private const SALES_COMMISSION = 0.1;

    private EntityManagerInterface $em;
    private LeadRepository $leadRepository;
    private GymRepository $gymRepository;
    private UserSubscriptionRepository $userSubscriptionRepository;
    private StripeConnectRepository $stripeConnectRepository;

    public function __construct(
        EntityManagerInterface $em,
        LeadRepository $leadRepository,
        GymRepository $gymRepository,
        UserSubscriptionRepository $userSubscriptionRepository,
        StripeConnectRepository $stripeConnectRepository
    ) {
        $this->em = $em;
        $this->leadRepository = $leadRepository;
        $this->gymRepository = $gymRepository;
        $this->userSubscriptionRepository = $userSubscriptionRepository;
        $this->stripeConnectRepository = $stripeConnectRepository;
    }

    /** @return array<string, mixed> */
    public function getData(\DateTime $start, \DateTime $end): array
    {
        return [
            'sales' => $this->getSalesByAssistants($start, $end),
            'connect' => $this->getConnectFees($start, $end),
            'subscriptions' => $this->getSubscriptionData()
        ];
    }

    /** @return array<string, mixed> */
    private function getConnectFees(\DateTime $start, \DateTime $end): array
    {
        $connectFees = $this
            ->stripeConnectRepository
            ->getConnectFeesBetweenDates($start, $end);

        if (!isset($connectFees['fees'])) {
            return [];
        }

        $fees = collect($connectFees['fees'])
            ->groupBy('currency')
            ->map(function($fee) {
                return [
                    'amount' => $fee->sum('amount'),
                    'count' => $fee->count()
                ];
            });

        return $fees->toArray();
    }

    /** @return array<string, mixed> */
    private function getSubscriptionData(): array
    {
        $userSubscriptions = $this
            ->userSubscriptionRepository
            ->getAllSubscriptions();

        $activeSubscriptions = collect($userSubscriptions)
            ->filter(function(UserSubscription $us) {
                return $us->getCanceled() === false;
            })->count();

        $thisMonthStart = new \DateTime('first day of this month');
        $lastMonthStart = new \DateTime('first day of last month');
        $start = strtotime($lastMonthStart->format('Y-m-d'));
        $end = strtotime($thisMonthStart->format('Y-m-d'));

        $churnedCustomersLastMonth = collect($userSubscriptions)
            ->filter(function(UserSubscription $us) use ($start, $end) {
                $sevenDaysAfterSubscribing = $us->getSubscribedDate() !== null ? strtotime($us->getSubscribedDate()->modify('+7 days')->format('Y-m-d')) : null;
                return $us->getCanceledAt() > $start
                    && $us->getCanceledAt() < $end
                    && $us->getCanceledAt() > $sevenDaysAfterSubscribing;
            })->count();

        $newCustomersLastMonth = collect($userSubscriptions)
            ->filter(function(UserSubscription $us) use ($thisMonthStart, $lastMonthStart) {
                return $us->getSubscribedDate() > $lastMonthStart
                    && $us->getSubscribedDate() < $thisMonthStart
                    && $us->getCanceled() === false;
            })->count();

        return [
            'total' => $activeSubscriptions,
            'churnedCustomersLastMonth' => $churnedCustomersLastMonth,
            'newCustomersLastMonth' => $newCustomersLastMonth
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getAllGymLeads(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $leads = [];

        $gyms = $this
            ->gymRepository
            ->findAll();

        foreach ($gyms as $gym) {
            $leadsQuery = $this
                ->leadRepository
                ->getLeadsByGym($gym, 0, -1, null, false, $start, $end);

            foreach ($leadsQuery as $lead) {
                $leads[] = [
                    'id' => $lead['id'],
                    'status' => $lead['status'],
                    'tags' => $lead['tags'],
                    'client' => $lead['client'],
                    'createdAt' => $lead['createdAt'],
                    'autoAssignLeads' => $gym->getAutoAssignLeads(),
                    'gymId' => $gym->getId(),
                    'trainer' => $lead['user']->getId()
                ];
            }
        }

        return $leads;
    }

    /** @return array<string, mixed> */
    private function getSalesByAssistants(\DateTime $start, \DateTime $end): array
    {
        $leads = $this->getAllGymLeads($start, $end);

        $collection = collect($leads)
            ->filter(function($lead) {
                return $lead['status'] === Lead::LEAD_WON;
            })
            ->map(function($lead) {
                $paymentsLog = $lead['client']['paymentsLog'];
                $successfulPayments = collect($paymentsLog)
                    ->filter(function($log) {
                        return $log['type'] === PaymentsLog::PAYMENT_SUCCEEDED;
                    });

                return [
                    'revenue' => $successfulPayments->sum('amount'),
                    'currency' => isset($paymentsLog[0]) ? $paymentsLog[0]['currency'] : '',
                    'owner' => !empty($lead['tags']) ? $lead['tags'][0] : '',
                    'feePercentage' => $lead['client']['trainer']['userStripe']['feePercentage'],
                    'trainer' => $lead['client']['trainer']['name']
                ];
            })
            ->groupBy('owner')
            ->mapWithKeys(function($values, $owner) {
                return [$owner => [
                    'count' => $values->count(),
                    'revenue' => $values->sum('revenue'),
                    'currency' => $values[0]['currency'],
                    'zfCommission' => $values->sum('revenue') * $values[0]['feePercentage'] / 100,
                    'salesCommission' => $values->sum('revenue') * self::SALES_COMMISSION,
                    'trainer' => $values[0]['trainer']
                ]];
            });

        return [
            'count' => $collection->sum('count'),
            'revenue' => $collection->sum('revenue'),
            'zfCommission' => $collection->sum('zfCommission'),
            'currency' => count($collection) > 0 ? $collection->first()['currency'] : '',
            'employee' => $collection->toArray()
        ];
    }
}
