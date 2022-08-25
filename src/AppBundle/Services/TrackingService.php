<?php

namespace AppBundle\Services;

use AppBundle\Security\CurrentUserFetcher;
use Mixpanel;

class TrackingService
{
    private Mixpanel $mp;

    public function __construct(
        string $mixpanelKey,
        string $env,
        private CurrentUserFetcher $currentUserFetcher
    ) {
        $this->mp = Mixpanel::getInstance($mixpanelKey, ['debug' => $env !== 'prod']);
    }

    /** @param array<mixed> $properties */
    public function fireEvent(string $event, array $properties = []): void
    {
        $this->mp->track($event, $properties);
    }

    public function updateUserProfile(): void
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        $this->mp->people->set(intval($user->getId()), [
            '$email' => $user->getEmail(),
            'totalClients' => $user->getTotalActiveClients(true),
            'stripeConnect' => $user->getUserStripe() !== null
        ]);
    }
}
