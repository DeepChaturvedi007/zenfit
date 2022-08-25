<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Subscription;
use AppBundle\Repository\SubscriptionRepository;

class SubscriptionsFixturesLoader
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository
    ) {}

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->subscriptionRepository->findOneBy(['title' => $item[0]]);
            if ($object !== null) {
                continue;
            }

            $object = new Subscription($item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6]);
            $this->subscriptionRepository->persist($object);
        }

        $this->subscriptionRepository->flush();
    }

    /* @phpstan-ignore-next-line */
    private function getData(): array
    {
        return
            [
                ['None', 'dk', 975, 'test', '123123', 'dkk', 25],
                ['EUR - TEST', 'eu', 120, 'test-eur', 'price_1I5okHJjIZC19I1e9NUHr8td', 'eur', 0],
                ['SEK - TEST', 'eu', 1425, 'test-sek', 'price_1HWKFCJjIZC19I1ebF5M31f0', 'sek', 0],
                ['DKK - TEST', 'dk', 975, 'test-dkk', 'price_1HifS5JjIZC19I1eTCrKpsnY', 'dkk', 25],
            ];
    }
}
