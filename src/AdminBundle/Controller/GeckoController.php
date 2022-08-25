<?php

namespace AdminBundle\Controller;

use AppBundle\Security\CurrentUserFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GeckoController extends AbstractController
{
    public function __construct(
        private CurrentUserFetcher $currentUserFetcher,
    ) { }

    public function getAction(Request $request): JsonResponse
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();
            abort_unless(is_admin($user), 403, 'Access denied');

            $start = $request->query->get('start');
            $end = $request->query->get('end');

            if ($start == null||$end == null) {
                throw new BadRequestHttpException('Start or end date wrong.');
            }

            $start = new \DateTime($start);
            $end = new \DateTime($end);

            return new JsonResponse(['sales' => [], 'connect' => [], 'subcriptions' => []]);

            //return new JsonResponse($this->adminService->getData($start, $end));
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
