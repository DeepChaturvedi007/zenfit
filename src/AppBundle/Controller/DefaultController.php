<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\ClientService;
use AppBundle\Services\DashboardService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Repository\ClientTagRepository;

#[Route("/dashboard")]
class DefaultController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private ClientService $clientService,
        private ClientTagRepository $clientTagRepository,
        private CurrentUserFetcher $currentUserFetcher,
        private string $stripeConnect,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($em, $tokenStorage);
    }

    #[Route("", name: "dashboardOverview")]
    public function dashboardOverviewAction(): Response
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if (!$user->getActivated()) {
            return $this->redirectToRoute('intro');
        }

        $payments = [
            'revenue' => $this->dashboardService->getRevenue($user),
            'total' => $this->dashboardService->getYearlyRevenueTotal($user),
            'connected' => !!$user->getUserStripe(),
            'stripeConnectUrl' => $this->stripeConnect,
        ];

        return $this->render('@App/default/dashboard.html.twig', [
            'metrics' => $this->dashboardService->getMetrics($user),
            'payments' => $payments
        ]);
    }

    #[Route("/clients", name: "clients")]
    public function clientsAction(Request $request): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if (!$currentUser->getActivated()) {
            return $this->redirectToRoute('intro');
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $q = $request->query->get('q');

        $clientService = $this->clientService;
        $qb = $clientService->getClientsQueryBuilder($user);

        if ($currentUser->isAssistant()) {
            $qb->innerJoin('c.tags', 't')
                ->andWhere('t.title = :tagTitle')
                ->setParameter('tagTitle', $currentUser->getFirstName());
        }

        $activeCount = (clone $qb)
            ->select('COUNT(c.id)')
            ->andWhere('c.active = 1')
            ->andWhere('c.name like :query')
            ->setParameter('query', '%' . $q . '%')
            ->getQuery()
            ->getSingleScalarResult();

        $inactiveCount = (clone $qb)
            ->select('COUNT(c.id)')
            ->andWhere('c.active = 0')
            ->andWhere('c.name like :query')
            ->setParameter('query', '%' . $q . '%')
            ->getQuery()
            ->getSingleScalarResult();

        $assistantsTags = [];
        if (!$user->isAssistant()) {
            $assistants = $user->getAllAssistants();
            foreach ($assistants as $assistant) {
                $assistantsTags[] = $assistant->getFirstName();
            }
        }

        $tags = collect($this->clientTagRepository->getAllTagsByUser($user))
            ->map(function($tag) {
                return $tag['title'];
            })
            ->toArray();

        return $this->render('@App/default/clients.html.twig', [
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'tagsList' => collect($assistantsTags)->concat($tags)->unique()->values()->toArray()
        ]);
    }

    #[Route("/leads", name: "leads")]
    public function leadsAction(): Response
    {
        return $this->render('@App/default/leads.html.twig');
    }
}
