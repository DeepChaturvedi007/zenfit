<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\User;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\LeadRepository;
use League\Fractal\TransformerAbstract;
use AppBundle\Repository\ClientStripeRepository;

class UserTransformer extends TransformerAbstract
{
    public function __construct(
        private ClientRepository $clientRepository,
        private LeadRepository $leadRepository,
        private ClientStripeRepository $clientStripeRepository
    ) {}

    /** @return array<string, mixed> */
    public function transform(User $user, \DateTime $start = null, \DateTime $end = null): array
    {
        try {
            $isAdmin = $user->getGymAdmin()->getId() === $user->getId();
        } catch (\Exception $e) {
            $isAdmin = false;
        }

        $leadCount = 0;
        $clientCount = 0;
        $clientStripeMetrics = [];

        if ($start !== null && $end !== null) {
            $leadCount = $this
                ->leadRepository
                ->findAllLeadsByUser($user, null, null, null, null, null, true, $start, $end);

            $clientCount = $this
                ->clientRepository
                ->getClientsByUser($user, '', [], $start, $end);

            $clientCount = collect($clientCount)->count();
            $leadCount = $leadCount['all'];

            $clientStripeMetrics = $this
                ->clientStripeRepository
                ->getClientStripeMetricsByUser($user, $start, $end);
        }

        $language = $user->getLanguage();
        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'email' => $user->getEmail(),
            'locale' => $language !== null ? $language->getLocale() : null,
            'name' => $user->getName(),
            'clients' => $clientCount,
            'leads' => $leadCount,
            'token' => $user->getInteractiveToken(),
            'isAdmin' => $isAdmin,
            'assignLeads' => $user->getAssignLeads(),
            'metrics' => $clientStripeMetrics
        ];
    }
}
