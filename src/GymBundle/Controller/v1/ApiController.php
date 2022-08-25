<?php declare(strict_types=1);

namespace GymBundle\Controller\v1;

use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use GymBundle\Controller\Controller as Controller;
use GymBundle\Services\TrainerService;
use GymBundle\Repository\GymRepository;
use AppBundle\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

/**
 * @Route("/v1/api")
 */
class ApiController extends Controller
{
    private TrainerService $trainerService;
    private GymRepository $gymRepository;
    private UserRepository $userRepository;
    private LeadRepository $leadRepository;
    private ClientRepository $clientRepository;
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        TrainerService $trainerService,
        ClientRepository $clientRepository,
        LeadRepository $leadRepository,
        GymRepository $gymRepository,
        UserRepository $userRepository
    )
    {
        $this->trainerService = $trainerService;
        $this->gymRepository = $gymRepository;
        $this->userRepository = $userRepository;
        $this->leadRepository = $leadRepository;
        $this->clientRepository = $clientRepository;
        $this->em = $em;

        parent::__construct($em);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $user = $this->requestTrainer($request);
        if ($user === null) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $count = (bool) $request->get('count');
            $gym = $this
                ->gymRepository
                ->findGymByAdmin($user);

            if ($gym === null) {
                throw new AccessDeniedHttpException('Current user does not administrate any Gym');
            }

            if ($count) {
                return new JsonResponse(['count' => $gym->getUsers()->count()]);
            }

            $trainers = $this
                ->gymRepository
                ->getTrainersByGym($gym);

            return new JsonResponse($trainers);
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("", methods={"DELETE"})
     */
    public function deleteAction(Request $request): JsonResponse
    {
        try {
            $user = $this->requestTrainer($request);
            if (!$user instanceof User) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $gymAdministratedByCurrentUser = $this
                ->gymRepository
                ->findGymByAdmin($user);

            if ($gymAdministratedByCurrentUser === null) {
                throw new AccessDeniedHttpException('Current user is not an admin of any Gym');
            }

            $id = (int) $request->get('id');
            $type = (string) $request->get('type');
            if ($id === 0 || $type === '') {
                throw new BadRequestHttpException('Please provide Id and Type');
            }

            $entity = null;

            $canDelete = false;

            switch ($type) {
              case 'trainer':
                $entity = $this
                    ->userRepository
                    ->find($id);

                  if ($entity === null) {
                      throw new NotFoundHttpException();
                  }
                  if ($entity === $user) {
                      $canDelete = false;
                  } elseif (in_array($gymAdministratedByCurrentUser, $entity->getGyms(), true)) {
                      $canDelete = true;
                  }
                  break;
              case 'lead':
                  $entity = $this
                      ->leadRepository
                      ->find($id);
                  if ($entity === null) {
                      throw new NotFoundHttpException();
                  }

                  if ($entity->getUser() === $user) {
                      $canDelete = true;
                  } elseif (in_array($gymAdministratedByCurrentUser, $entity->getUser()->getGyms(), true)) {
                      $canDelete = true;
                  }
                  break;
              case 'client':
                  $entity = $this
                      ->clientRepository
                      ->find($id);
                  if ($entity === null) {
                      throw new NotFoundHttpException();
                  }

                  if ($entity->getUser() === $user) {
                      $canDelete = true;
                  } elseif (in_array($gymAdministratedByCurrentUser, $entity->getUser()->getGyms(), true)) {
                      $canDelete = true;
                  }
                  break;
            }

            if (!$canDelete) {
                throw new AccessDeniedHttpException('Access denied');
            }

            if ($entity === null) {
                throw new NotFoundHttpException();
            }

            $entity->setDeleted(true);
            $this->em->flush();

            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $body = $this->requestInput($request);

        try {
            $admin = null;
            if(isset($body->admin)) {
                $admin = $this
                    ->userRepository
                    ->findByToken($body->admin);
            }

            $this
                ->trainerService
                ->create($body->name, $body->email, $body->password, null, $admin);

            return new JsonResponse(['msg' => 'User was created']);
        } catch (\Exception $e) {
            return new JsonResponse([
              'error' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
              'error' => 'A server error occurred.',
              'msg' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @Route("/leads", methods={"GET"})
     */
    public function getLeadsAction(Request $request): JsonResponse
    {
        try {
            $user = $this->requestTrainer($request);
            if (!$user instanceof User) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $query = (string) $request->get('q');
            $count = (bool) $request->get('count');
            $offset = (int) $request->get('offset', 0);
            $limit = (int) $request->get('limit', 30);

            $gym = $this
                ->gymRepository
                ->findGymByAdmin($user);

            if ($gym === null) {
                throw new NotFoundHttpException('Current user is not an admin of any gym');
            }

            $leads = $this
                ->leadRepository
                ->getLeadsByGym($gym, $offset, $limit, $query, $count);

            if ($count) {
                $leads = array_pop($leads);
            }

            return new JsonResponse($leads);
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("/clients", methods={"GET"})
     */
    public function getClientsAction(Request $request): JsonResponse
    {
        try {
            $user = $this->requestTrainer($request);
            if (!$user instanceof User) {
                throw new AccessDeniedHttpException('Access denied');
            }

            $count = (bool) $request->get('count');
            $query = (string) $request->get('q');
            $offset = (int) $request->get('offset', 0);
            $limit = (int) $request->get('limit', 30);

            $gym = $this
                ->gymRepository
                ->findGymByAdmin($user);

            if ($gym === null) {
                throw new NotFoundHttpException('Current user is not an admin of any gym');
            }

            $clients = $this
                ->clientRepository
                ->getClientsByGym($gym, $offset, $limit, $query, $count);

            if ($count) {
                $clients = array_pop($clients);
            }

            return new JsonResponse($clients);
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        } catch (\Throwable $e) {
            return new JsonResponse([], 422);
        }
    }

}
