<?php

namespace ProgressBundle\Services;

use AppBundle\Entity\BodyProgress;
use AppBundle\Entity\ClientStatus;
use AppBundle\PlaceholderProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Client;
use AppBundle\Entity\Event;
use Carbon\Carbon;

class ClientProgressService implements PlaceholderProviderInterface
{
    private EntityManagerInterface $em;

    /**
     * @var Client $client
     */
    private $client;

    const TYPE_WEIGHT_LOSS = 'lose';
    const TYPE_MUSCLE_GAIN = 'gain';

    const PRIMARY_GOAL_LOSE_1KG = 1;
    const PRIMARY_GOAL_LOSE_05KG = 2;
    const PRIMARY_GOAL_MAINTAIN = 3;
    const PRIMARY_GOAL_GAIN_05KG = 4;
    const PRIMARY_GOAL_GAIN_1KG = 5;

    const MEASURING_SYSTEM_METRIC = 1;
    const MEASURING_SYSTEM_IMPERIAL = 2;

    const PRIMARY_GOAL_UNITS = [
        self::MEASURING_SYSTEM_METRIC => [
            1 => -1,
            2 => -0.5,
            3 => 0,
            4 => 0.5,
            5 => 1
        ],
        self::MEASURING_SYSTEM_IMPERIAL => [
            1 => -2,
            2 => -1,
            3 => 0,
            4 => 1,
            5 => 2
        ]
    ];

    //units
    protected $circumferenceUnit;
    protected $weightUnit;

    //placeholders
    private $weightStart = .0;
    private $weightEnd = .0;
    private $circumferenceTotalStart = .0;
    private $circumferenceTotalEnd = .0;
    private $fatStart = .0;
    private $fatEnd = .0;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $entries;

    const PLACEHOLDER_WEIGHT_START = 'weightStart';
    const PLACEHOLDER_WEIGHT_END = 'weightEnd';
    const PLACEHOLDER_CIRCUM_START = 'circumStart';
    const PLACEHOLDER_CIRCUM_END = 'circumEnd';
    const PLACEHOLDER_GOAL = 'goal';
    const PLACEHOLDER_DIFF = 'diff';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->entries = collect();
    }

    public function getPlaceholderLabels(): array
    {
        return [
            self::PLACEHOLDER_WEIGHT_START,
            self::PLACEHOLDER_WEIGHT_END,
            self::PLACEHOLDER_CIRCUM_START,
            self::PLACEHOLDER_CIRCUM_END,
            self::PLACEHOLDER_GOAL,
            self::PLACEHOLDER_DIFF,
        ];
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setUnits()
    {
        $this->weightUnit = 'kg';
        $this->circumferenceUnit = 'cm';

        if ($this->client->isImperialMeasuringSystem()) {
            $this->weightUnit = 'lbs';
            $this->circumferenceUnit = 'in';
        }

        return $this;
    }

    /**
     * @param float $weightStart
     *
     * @return $this
     */
    public function setWeightStart($weightStart)
    {
        $this->weightStart = $weightStart;
        return $this;
    }

    /**
     * @param float $weightEnd
     *
     * @return $this
     */
    public function setWeightEnd($weightEnd)
    {
        $this->weightEnd = $weightEnd;
        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setFatStart($value)
    {
        $this->fatStart = $value;
        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setFatEnd($value)
    {
        $this->fatEnd = $value;
        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setCircumferenceTotalStart($value)
    {
        $this->circumferenceTotalStart = $value;
        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setCircumferenceTotalEnd($value)
    {
        $this->circumferenceTotalEnd = $value;
        return $this;
    }

    /**
     * @param \Illuminate\Support\Collection $entries
     *
     * @return $this
     */
    public function setEntries(\Illuminate\Support\Collection $entries)
    {
        $this->entries = $entries;
        return $this;
    }

    public function getClientGoalWeight(): ?float
    {
        return $this->client->getGoalWeight();
    }

    public function getDirection(): string
    {
        return $this->getClientGoalWeight() < $this->weightStart ? self::TYPE_WEIGHT_LOSS : self::TYPE_MUSCLE_GAIN;
    }

    public function didHitGoal()
    {
        $progress = $this->weightEnd - $this->weightStart;
        $percentage = $this->getPercentageProgress($progress);

        if ($percentage >= 1) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getTotalEntries()
    {
        return rescue(function () {
            return (int) $this->em
                ->getRepository(BodyProgress::class)
                ->countByClient($this->client);
        }, 0);
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     * @param string $order
     *
     * @return \Illuminate\Support\Collection
     */
    private function getBodyProgressEntries($limit = null, $offset = null, $order = 'ASC', $excludeNullFields = null)
    {
        $repo = $this->em->getRepository(BodyProgress::class);
        $entries = collect($repo->getEntriesByClient($this->client, $limit, $offset, $order, $excludeNullFields));
        $totalFields = [
            'chest', 'waist', 'hips', 'glutes',
            'leftArm', 'rightArm', 'rightThigh', 'leftThigh', 'leftCalf', 'rightCalf',
        ];

        return $entries->map(function ($item) use ($totalFields) {
            $item = collect($item);
            return $item
                ->put('date', date_format($item->get('date'), 'M d, Y'))
                ->put('total', $item->only($totalFields)->sum());
        });
    }

    /**
     * @param int $limit
     * @param int|null $offset
     * @param string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLastEntries($limit = 5, $offset = null, $order = 'ASC', $excludeNullFields = null)
    {
        return $this->getBodyProgressEntries($limit, $offset, $order, $excludeNullFields);
    }

    /**
     * @return $this
     */
    public function setProgressValues()
    {
        $this->setEntries($this->getBodyProgressEntries());

        $lastEntry = $this->entries->last();

        $firstWeight = rescue(function () {
            return $this->entries->firstWhere('weight', '>', 0)->get('weight');
        }, 0);

        $firstFat = rescue(function () {
            return $this->entries->firstWhere('fat', '>', 0)->get('fat');
        }, 0);

        $firstCircumference = rescue(function () {
            return $this->entries->firstWhere('total', '>', 0)->get('total');
        }, 0);

        /**
         * @param string $property
         * @return mixed
         */
        $getLastRealValue = function ($property) {
            $entry = $this->entries->last(function ($entry) use ($property) {
                return $entry[$property] > 0;
            });
            return $entry ? $entry->get($property) : 0;
        };

        $lastWeight = $getLastRealValue('weight');
        $lastFat = $getLastRealValue('fat');
        $lastCircumference = $getLastRealValue('total');

        $this
            ->setWeightStart((float)$firstWeight)
            ->setWeightEnd((float)$lastWeight)
            ->setFatStart((float)$firstFat)
            ->setFatEnd((float)$lastFat)
            ->setCircumferenceTotalStart((float)$firstCircumference)
            ->setCircumferenceTotalEnd((float)$lastCircumference);

        return $this;
    }

    /**
     * @param int|float $progress
     *
     * @return float|int
     */
    private function getPercentageProgress($progress)
    {
        if ($progress === 0) {
            return 0;
        }

        $diff = $this->getClientGoalWeight() - $this->weightStart;
        $percentage = $diff == 0 ? 0 : $progress / $diff;

        if ($percentage > 1) {
            return 1;
        } elseif ($percentage > 0) {
            return $percentage;
        } else {
            return 0;
        }
    }

    public function getProgress(): array
    {
        if (!$this->entries) {
            $this->setProgressValues();
        }

        $progress = $this->weightEnd - $this->weightStart;
        $percentage = $this->getPercentageProgress($progress);
        $direction = $this->getDirection();

        $goal = $this->getClientGoalWeight();
        $left = round(abs($goal - $this->weightEnd), 1);
        if ($direction === self::TYPE_MUSCLE_GAIN && $this->weightEnd > $goal) {
            $left = 0;
        } else if ($direction === self::TYPE_WEIGHT_LOSS && $this->weightEnd < $goal) {
            $left = 0;
        }

        $progressMetrics = collect($this->getWeightProgress())
            ->only(['lastWeek', 'now', 'offText', 'progressText', 'unit'])
            ->all();

        return [
            'goal' => $goal,
            'start' => $this->weightStart,
            'last' => $this->weightEnd,
            'percentage' => round($percentage * 100),
            'unit' => $this->weightUnit,
            'weeks' => $this->getWeeks()['totalWeeks'],
            'currentWeek' => $this->getWeeks()['currentWeek'],
            'entries' => $this->entries,
            'left' => $left,
            'direction' => $direction,
            'progress' => round(abs($progress), 1),
            'weekly' => $progressMetrics['now'],
            'lastWeek' => $progressMetrics['lastWeek'],
            'offText' => $progressMetrics['offText'],
            'progressText' => $progressMetrics['progressText']
        ];
    }

    /** @return array<string, mixed> */
    public function getAvgProgressScores(): array
    {
        $primaryGoal = $this->client->getPrimaryGoal();

        if (!$primaryGoal) {
            return [];
        }

        $measuringSystem = $this->client->getMeasuringSystem() ? $this->client->getMeasuringSystem() : self::MEASURING_SYSTEM_METRIC;
        $threshold = self::PRIMARY_GOAL_UNITS[$measuringSystem][$primaryGoal];
        $direction = $this->getDirection();

        $entries = $this->entries
            ->map(function($entry) {
                return [
                    'weight' => $entry->get('weight'),
                    'date' => $entry->get('date')
                ];
            })
            ->filter(function($entry) {
                return $entry['weight'] != null;
            })
            ->values();

        return $entries
            ->map(function($entry, $key) use ($entries, $threshold, $direction) {
                $prevWeight = $entries->get(--$key);
                if ($prevWeight === null) {
                    return false;
                }

                $currentWeight = (float) $entry['weight'];
                $date = $entry['date'];
                $prevWeight = (float) $prevWeight['weight'];

                if ($direction === self::TYPE_WEIGHT_LOSS) {
                    //client goal is to lose weight
                    if (($currentWeight - $threshold) <= $prevWeight) {
                        return ['score' => 5, 'date' => $date];
                    }
                    if ($currentWeight < $prevWeight) {
                        return ['score' => 4, 'date' => $date];
                    }
                    if ($currentWeight == $prevWeight) {
                        return ['score' => 3, 'date' => $date];
                    }
                    if (($currentWeight + $threshold) > $prevWeight) {
                        return ['score' => 2, 'date' => $date];
                    }
                    if ($currentWeight > $prevWeight) {
                        return ['score' => 1, 'date' => $date];
                    }
                } else {
                    //client goal is to put on weight
                    if (($currentWeight + $threshold) > $prevWeight) {
                        return ['score' => 5, 'date' => $date];
                    }
                    if ($currentWeight > $prevWeight) {
                        return ['score' => 4, 'date' => $date];
                    }
                    if ($currentWeight == $prevWeight) {
                        return ['score' => 3, 'date' => $date];
                    }
                    if (($currentWeight - $threshold) < $prevWeight) {
                        return ['score' => 2, 'date' => $date];
                    }
                    if ($currentWeight < $prevWeight) {
                        return ['score' => 1, 'date' => $date];
                    }
                }
            })
            ->reject(function($val) {
                return $val === false;
            })
            ->mapWithKeys(function($entry) {
                return [$entry['date'] => $entry['score']];
            })
            ->groupBy(function($key, $date) {
                $startDate = Carbon::parse($this->client->getStartDate() ?? $this->client->getCreatedAt());
                $checkInDate = Carbon::parse($date);
                return $checkInDate->diffInWeeks($startDate);
            })
            ->map(function($entry) {
                return round($entry->avg(), 1);
            })->toArray();
    }

    /**
     * @return array
     */
    public function getCircumferenceProgress()
    {
        static $results = null;

        if (null === $results) {
            $results = $this->getKpiProgress(
                $this->circumferenceTotalStart,
                $this->circumferenceTotalEnd,
                'total',
                $this->circumferenceUnit
            );
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getFatProgress()
    {
        static $results = null;

        if (null === $results) {
            $results = $this->getKpiProgress(
                $this->fatStart,
                $this->fatEnd,
                'fat',
                '%'
            );
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getWeightProgress()
    {
        $results = $this->getKpiProgress(
            $this->weightStart,
            $this->weightEnd,
            'weight',
            $this->weightUnit
        );

        $goal = $this->getClientGoalWeight();

        $goalAbs = round(abs($goal - $this->weightStart), 1);
        $results['left'] = round(abs($goal - $this->weightEnd), 1);
        $results['goal'] = $goal;
        $results['direction'] = $this->getDirection();
        $results['progress'] = $goal ? $this->weightEnd - $this->weightStart : 0;

        if ($results['direction'] === self::TYPE_WEIGHT_LOSS) {
            $results['offText'] = $goal && $results['progress'] < 0 ? "of {$goalAbs} {$this->weightUnit}" : '';
        } else {
            $results['offText'] = $goal && $results['progress'] > 0 ? "of {$goalAbs} {$this->weightUnit}" : '';
        }

        if ($results['count'] > 1 && $results['progress'] !== 0 && $goal !== null) {
            $results['progressText'] = $results['progress'] < 0 ? "You’ve lost" : "You’ve gained";
        } else {
            $results['progressText'] = '';
        }

        return $results;

    }

    public function getWeeks(): array
    {
        $totalWeeks = 0;
        $currentWeek = 0;
        $endDate = $this->client->getEndDate();
        $startDate = $this->client->getStartDate();

        if ($endDate && $startDate && $endDate > $this->client->getStartDate()) {
            $interval = $endDate->diff($startDate);
            $totalWeeks = (int)($interval->days / 7) - 1;
            $currentWeek = (int)((new \DateTime())->diff($startDate)->days / 7) + 1;
            $currentWeek = $currentWeek > $totalWeeks ? $totalWeeks : $currentWeek;
        }

        return [
            'totalWeeks' => $totalWeeks,
            'currentWeek' => $currentWeek
        ];
    }

    /** @return array<mixed> */
    private function getKpiProgress(int|float $startValue, int|float $lastValue, string $field, string $unit): array
    {
        $lastWeekValue = 0;
        $prevLastWeekValue = 0;

        $entries = $this->entries->filter(function ($entry) use ($field) {
            return $entry[$field] > 0;
        });

        if ($entries->isNotEmpty()) {
            $startOfWeek = Carbon::parse($entries->last()->get('date'))->startOfWeek();

            //last week
            $lastWeekEntry = $entries
                ->filter(function (\Illuminate\Support\Collection $entry) use ($startOfWeek) {
                    $date = Carbon::parse($entry->get('date'));
                    return $startOfWeek->greaterThan($date);
                })
                ->last();

            if ($lastWeekEntry) {
                $lastWeekValue = (float)$lastWeekEntry->get($field, 0);

                //the week before last week
                $prevLastWeekEntry = $entries
                    ->filter(function (\Illuminate\Support\Collection $entry) use ($lastWeekEntry) {
                        $startOfLastWeek = Carbon::parse($lastWeekEntry->get('date'))->startOfWeek();
                        $date = Carbon::parse($entry->get('date'));
                        return $startOfLastWeek->greaterThan($date);
                    })
                    ->last();

                if($prevLastWeekEntry) {
                    $prevLastWeekValue = (float)$prevLastWeekEntry->get($field, 0);
                }
            }

        }

        return [
            'start' => $startValue,
            'last' => $lastValue,
            'total' => round($lastValue - $startValue, 1),
            'now' => $lastWeekValue ? round($lastValue - $lastWeekValue, 1) : 0,
            'lastWeek' => $prevLastWeekValue ? round($lastWeekValue - $prevLastWeekValue, 1) : 0,
            'unit' => $unit,
            'count' => $entries->count(),
        ];
    }

    /**
     * @return array
     */
    public function getProgressPlaceholders(): array
    {
        $diff = round(abs($this->client->getGoalWeight() - $this->weightEnd), 1);

        $weightFrom = null;
        $weightTo = null;

        if ($this->entries->isNotEmpty()) {
            $weightProgress = $this->entries
                ->pluck('weight')
                ->unique()
                ->filter(function ($value) {
                    return 0 != (float) $value;
                });

            $weightFrom = $weightProgress->slice(0, -1)->last();
            $weightTo = $weightProgress->last();
        }

        if (!$weightFrom) {
            $weightFrom = $this->weightStart;
        }

        if (!$weightTo) {
            $weightTo = $this->weightEnd;
        }

        return [
            self::PLACEHOLDER_WEIGHT_START => number_format($weightFrom, 1) . " " . $this->weightUnit,
            self::PLACEHOLDER_WEIGHT_END => number_format($weightTo, 1) . " " . $this->weightUnit,
            self::PLACEHOLDER_CIRCUM_START => $this->circumferenceTotalStart . " " . $this->circumferenceUnit,
            self::PLACEHOLDER_CIRCUM_END => $this->circumferenceTotalEnd . " " . $this->circumferenceUnit,
            self::PLACEHOLDER_GOAL => $this->client->getGoalWeight() . " " . $this->weightUnit,
            self::PLACEHOLDER_DIFF => $diff . " " . $this->weightUnit,
        ];
    }

    /**
     * @return array
     */
    public function getClientStatus()
    {
        $clientStatus = $this->em->getRepository(ClientStatus::class);
        return $clientStatus->getEventByClient($this->client, Event::UPDATED_BODYPROGRESS);
    }
}
