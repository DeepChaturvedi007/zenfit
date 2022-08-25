<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Repository\ProgressFeedbackRepository;
use ProgressBundle\Services\ClientProgressService;
use Carbon\Carbon;

class ClientScoreService
{
    private Client $client;
    /** @var array<int, mixed> */
    private array $weeks = [];

    private EntityManagerInterface $em;
    private ProgressFeedbackRepository $progressFeedbackRepository;
    private ClientProgressService $clientProgressService;

    public function __construct(
        EntityManagerInterface $em,
        ProgressFeedbackRepository $progressFeedbackRepository,
        ClientProgressService $clientProgressService
    ) {
        $this->em = $em;
        $this->progressFeedbackRepository = $progressFeedbackRepository;
        $this->clientProgressService = $clientProgressService;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function prepareWeeks(): self
    {
        $startDate = Carbon::parse($this->client->getStartDate() ?? $this->client->getCreatedAt());
        $today = Carbon::parse('now');
        $weeksSinceStart = $today->diffInWeeks($startDate);
        $weeks = [];

        for ($i = 1; $i < $weeksSinceStart; $i++) {
            $weeks[$i] = [
                'progressScore' => null,
                'checkInScore' => null
            ];
        }

        $this->weeks = $weeks;
        return $this;
    }

    /**
     * @param array<string, int> $newData
     * @param string $keyVal
     */
    private function mapToWeek(array $newData, string $keyVal): void
    {
        foreach ($newData as $key => $value) {
            $this->weeks[$key][$keyVal] = $value;
        }
    }

    /** @return array<string, mixed> */
    public function getScore(): array
    {
        $avgCheckIns = $this
            ->progressFeedbackRepository
            ->getAvgCheckInScoreByClient($this->client);

        $avgProgressScores = $this
            ->clientProgressService
            ->setClient($this->client)
            ->setProgressValues()
            ->setUnits()
            ->getAvgProgressScores();

        $this->mapToWeek($avgCheckIns, 'checkInScore');
        $this->mapToWeek($avgProgressScores, 'progressScore');

        $preparedWeeks = collect($this->weeks)
            ->map(function($entry) {
                return [
                    'progressScore' => $entry['progressScore'] ?? null,
                    'checkInScore' => $entry['checkInScore'] ?? null,
                    'total' => collect($entry)->avg()
                ];
            });

        return [
            'total' => round($preparedWeeks->average('total'), 1),
            'weeks' => $preparedWeeks->toArray()
        ];
    }


}
