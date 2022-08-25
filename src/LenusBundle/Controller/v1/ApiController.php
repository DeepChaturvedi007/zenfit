<?php declare(strict_types=1);

namespace LenusBundle\Controller\v1;

use AppBundle\Services\ErrorHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use AppBundle\Repository\UserRepository;

#[Route("/api")]
class ApiController extends Controller
{
    private const LENUS_TOKEN = 'OMh6riYjfT1fucfMtcbIWn9DIRLKRSYus6q';

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private ErrorHandlerService $errorHandlerService,
        private UserRepository $userRepository,
    ) {
        parent::__construct($em, $tokenStorage);
    }

    private function getLenusAuth(Request $request): bool
    {
        $header = $request->headers->get('Authorization');

        return $header === self::LENUS_TOKEN;
    }

    #[Route("/trainers", methods: ["GET"])]
    public function trainers(Request $request): JsonResponse
    {
        try {
            abort_unless($this->getLenusAuth($request), 403, 'Access denied');

            $start = $request->query->get('start');
            $end = $request->query->get('end');
            $country = $request->query->get('country');
            $limit = $request->query->getInt('limit', 10);
            $offset = $request->query->getInt('offset');
            $q = $request->query->get('q');

            if ($start === null || $end === null) {
                throw new BadRequestException('Start or end date wrong.', 400);
            }

            $start = new \DateTime($start);
            $end = new \DateTime($end);

            $users = $this
                ->userRepository
                ->getActiveUsers($offset, $limit, $q, $start, $end, $country);

            return new JsonResponse($users);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            $message = $e->getMessage();
            return new JsonResponse([
                'error' => $message
            ], 400);
        }
    }
}
