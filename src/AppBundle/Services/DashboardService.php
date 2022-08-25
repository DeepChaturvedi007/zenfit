<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\News;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Arr;
use DateTimeInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Lead;
use AppBundle\Entity\PaymentsLog;
use ProgressBundle\Services\ClientProgressService;
use Stripe\Balance;
use Stripe\Stripe;

class DashboardService
{
    public const DEFAULT_CURRENCY = 'usd';

    public function __construct(
        private EntityManagerInterface $em,
        private ClientProgressService $clientProgressService,
        private Stripe $stripe,
        private string $fixerApiKey,
        private string $stripeConnect,
    ) {}

    /**
     * @param User $user
     * @param int $year
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getYearlyRevenueTotal(User $user, $year = null)
    {
        if ($year === null) {
            $year = (int) date('Y');
        }

        $startFromMonth = '01';
        $startDate = new \DateTimeImmutable("$year-$startFromMonth-01T00:00:00");
        $endDate = $startDate->modify('last day of December this year')->setTime(23, 59, 59);

        $result = collect($this->em
            ->getRepository(PaymentsLog::class)
            ->findByUserAndDateRange($user, $startDate, $endDate));

        $currency = $result->firstWhere('currency')['currency'] ?? static::DEFAULT_CURRENCY;
        $total = $result->reduce(function ($carry, $payment) {
            $amount = (double) $payment['amount'];
            if ($payment['type'] === PaymentsLog::CHARGE_REFUNDED) {
                $amount = -$amount;
            }
            return $carry + $amount;
        }, .0);

        return [
            'total' => $total,
            'currency' => $currency,
            'year' => $startDate->format('Y'),
        ];
    }

    /** @return array<mixed> */
    public function getRevenue(User $user)
    {
        $userStripe = $user->getUserStripe();
        if ($userStripe === null) {
            return [];
        }

        $date = new \DateTimeImmutable();

        /**
         * @var \DateTimeImmutable $startDate
         * @var \DateTimeImmutable $prevMonthDate
         */
        $startDate = (clone $date)->modify("first day of this month - 6 months");
        $prevMonthDate = (clone $date)->modify('00:00:00 first day of previous month');
        $paymentsLogRepo = $this->em->getRepository(PaymentsLog::class);

        $payments = $paymentsLogRepo->findByUserAndDateRange($user, $startDate, $date);

        try {
            $stripeBalances = collect(Balance::retrieve(
                ['stripe_account' => $userStripe->getStripeUserId()]
            ));

            $balances['available'] = collect($stripeBalances->get('available'))
                ->map(function($val) {
                    return [
                        'amount' => $val['amount'] / 100,
                        'currency' => $val['currency']
                    ];
                })->toArray();

            $balances['pending'] = collect($stripeBalances->get('pending'))
                ->map(function($val) {
                    return [
                        'amount' => $val['amount'] / 100,
                        'currency' => $val['currency']
                    ];
                })->toArray();
        } catch (\Exception $e) {
            $balances['available'] = [];
            $balances['pending'] = [];
        }


        //$streams = $paymentsLogRepo->getRevenueStreams($user, $startDate, $date);

        $chart = [];
        $metrics = [
            'last' => [
                'total' => .0,
                'percentage_change' => .0,
            ],
            'current' => [
                'total' => .0,
                'percentage_change' => .0,
            ],
        ];

        //get most used currency
        //this currency we'll use
        //in case payments in other currencies are made
        //we convert into this default currency
        $currency = $this->getCurrency($payments) ?? static::DEFAULT_CURRENCY;

        $hashCurrent = $date->format('Y-m');
        $hashLast = $prevMonthDate->format('Y-m');

        foreach ($payments as $payment) {
            //check if currency is the same as the one
            //that we'll use
            if ($payment['currency'] != $currency['base']) {
                $payment = $this->convertPaymentIntoNewCurrency($payment, $currency);
            }

            $amount = $this->getPaymentAmount($payment);

            /**
             * @var \DateTime $date
             */
            $date = $payment['createdAt'];

            // Chart
            $month = (int) $date->format('n');
            $prev = $chart[$month] ?? [];
            $total = Arr::get($prev, 'total', 0) + $amount;

            $chart[$month] = [
                'total' => round($total),
                'currency' => $currency['base'],
                'date' => $date->format('Y-m-d')
            ];

            // Metrics

            $hash = $date->format('Y-m');
            $group = null;

            if ($hash === $hashLast) {
                $group = 'last';
            } else if ($hash === $hashCurrent) {
                $group = 'current';
            }

            if (null === $group) {
                continue;
            }

            if (!isset($metrics[$group])) {
                $metrics[$group] = [
                    'total' => 0,
                    'percentage_change' => 0,
                ];
            }

            $metrics[$group]['total'] += $amount;
            $prevTotal = Arr::get($chart[($month) - 1] ?? [], 'total', 0);

            if ($prevTotal > 0) {
                $metrics[$group]['percentage_change'] = round(($total - $prevTotal) / $prevTotal * 100);
            }
        }

        $total = collect($chart)->sum('total');

        $goalProgress = rescue(function () use ($user, $metrics) {
            if ($user->getMonthlyGoal() > 0) {
                $value = ceil($metrics['current']['total'] / $user->getMonthlyGoal() * 100);
            } else {
                $value = 0;
            }

            return min(max(0, $value), 100);
        }, 0);

        $goal = [
            'total' => $user->getMonthlyGoal(),
            'charged' => $metrics['current']['total'],
            'progress' => $goalProgress,
        ];

        $currency = $currency['base'];
        return compact('chart', 'metrics', 'currency', 'goal', 'total', 'balances');
    }

    private function getCurrency($payments)
    {
        $currencies = [];
        foreach ($payments as $payment) {
            $currencies[] = $payment['currency'];
        }
        $values = array_count_values($currencies);
        arsort($values);

        $baseCurrency = count($values) > 0 ? array_slice(array_keys($values), 0, 1, true)[0] : static::DEFAULT_CURRENCY;
        $response = ['base' => $baseCurrency];

        //if only one currency, just use that one
        if (sizeof($values) == 1) {
            return $response;
        }

        //get exchange rates for all currencies
        //where base is default currency
        $apiKey = $this->fixerApiKey;
        foreach ($values as $key => $val) {
            try {
                $json = file_get_contents("https://data.fixer.io/api/latest?access_key={$apiKey}&base={$key}&symbols={$baseCurrency}");
                $obj = json_decode($json, true);
                if (isset($obj['rates'][strtoupper($baseCurrency)])) {
                    $response['others'][$key] = $obj['rates'][strtoupper($baseCurrency)];
                } else {
                    $response['others'][$key] = 1;
                }
            } catch (\Exception $e) {
                $response['others'][$key] = 1;
            }
        }

        return $response;
    }

    private function convertPaymentIntoNewCurrency($payment, $currency)
    {
        $exchangeRate = $currency['others'][$payment['currency']];
        $payment['amount'] = $exchangeRate * $payment['amount'];
        $payment['currency'] = $currency['base'];
        return $payment;
    }


    public function getPaymentAmount($payment)
    {
        $amount = (double) $payment['amount'];

        $amount = match ($payment['type']) {
            PaymentsLog::PAYMENT_SUCCEEDED => $amount,
            PaymentsLog::CHARGE_REFUNDED => -$amount,
            default => $amount,
        };

        return $amount;
    }

    /**
     * Finds news by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param mixed[]       $criteria
     * @param string[]|null $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return object[] The objects.
     *
     * @throws UnexpectedValueException
     */
    public function getNews(array $criteria = [], array $orderBy = ['date' => 'DESC'], $limit = 10, $offset = 0)
    {
        return $this->em
            ->getRepository(News::class)
            ->findBy($criteria, $orderBy, $limit, $offset);
    }

    /** @return array<string, mixed> */
    public function getMetrics(User $user): array
    {
        $leads = $this
            ->em
            ->getRepository(Lead::class)
            ->getByUser($user);

        $clients = $this
            ->em
            ->getRepository(Client::class)
            ->getByUser($user);

        return [
            'leads' => $this->getEntriesByMonth($leads, false),
            'clients' => $this->getEntriesByMonth($clients, false, true),
            'conversion' => $this->getEntriesByMonth($leads, true),
            'successRate' => $this->getSuccessRate($user),
            'stripeConnect' => [
                'connected' => $user->getUserStripe() !== null,
                'url' => $this->stripeConnect
            ],
            'clientCount' => $user->getTotalActiveClients(true)
        ];
    }

    private function getSuccessRate(User $user)
    {
        $service = $this
            ->clientProgressService;

        $success = 0;
        $clients = $this->em
            ->getRepository(Client::class)
            ->getClientsThatHaveAGoal($user);

        foreach($clients as $client) {
            $didHitGoal = $service
                ->setClient($client)
                ->setProgressValues()
                ->setUnits()
                ->didHitGoal();

            if ($didHitGoal) {
                $success++;
            }
        }

        return [
            'success' => $success,
            'total' => count($clients)
        ];
    }

    private function getEntriesByMonth($entries, $conversionRate = false, $allTime = false)
    {
        //get dates
        $lastMonthStart = new \DateTime('first day of last month');
        $thisMonthStart = new \DateTime('first day of this month');

        //get data from last month and this month
        $lastMonth = collect($entries)
            ->filter(function($item) use ($thisMonthStart, $lastMonthStart, $allTime) {
                if ($allTime) return $item->getCreatedAt() < $thisMonthStart;
                return $item->getCreatedAt() > $lastMonthStart && $item->getCreatedAt() < $thisMonthStart;
            });

        if ($allTime) {
            $thisMonth = collect($entries);
        } else {
            $thisMonth = collect($entries)
                ->filter(function($item) use ($thisMonthStart) {
                    return $item->getCreatedAt() > $thisMonthStart;
                });
        }

        $lastMonthCount = $lastMonth->count();
        $thisMonthCount = $thisMonth->count();

        // if we are computing the conversion rate from new lead to won lead.
        if ($conversionRate) {
            $lastMonthWonCount = $lastMonth
                ->filter(function($item) {
                    return $item->getStatus() == Lead::LEAD_WON;
                })->count();

            $thisMonthWonCount = $thisMonth
                ->filter(function($item) {
                    return $item->getStatus() == Lead::LEAD_WON;
                })->count();

            $lastMonthCount = $lastMonthCount == 0 ? 0 : round($lastMonthWonCount / $lastMonthCount * 100);
            $thisMonthCount = $thisMonthCount == 0 ? 0 : round($thisMonthWonCount / $thisMonthCount * 100);
        }

        return [
            'lastMonth' => $lastMonthCount,
            'thisMonth' => $thisMonthCount,
            'percentage' => $lastMonthCount == 0 ? 0 : round(($thisMonthCount-$lastMonthCount)/$lastMonthCount * 100)
        ];
    }

}
