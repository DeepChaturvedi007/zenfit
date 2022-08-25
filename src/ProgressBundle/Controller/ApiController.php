<?php

namespace ProgressBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\BodyProgress;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\ClientImageService;
use Doctrine\ORM\EntityManagerInterface;
use ProgressBundle\Services\ClientProgressHelperService;
use ProgressBundle\Services\ClientProgressService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use AppBundle\Repository\BodyProgressRepository;
use AppBundle\Repository\ClientImageRepository;

class ApiController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private CurrentUserFetcher $currentUserFetcher,
        private SerializerInterface $serializer,
        private ClientImageService $clientImageService,
        private ClientProgressService $clientProgressService,
        private ClientProgressHelperService $clientProgressHelperService,
        private BodyProgressRepository $bodyProgressRepository,
        private ClientImageRepository $clientImageRepository
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/api/images/{client}", methods={"GET"}, name="progressImages")
     */
    public function getImagesAction(Client $client, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $page = $request->query->getInt('page', 1);
        $maxResults = $request->query->getInt('limit', 5);
        $offset = $maxResults * ($page - 1);

        $clientImages = $this
            ->clientImageRepository
            ->findByClient($client, $maxResults, $offset);

        return new JsonResponse($this->serializer->normalize($clientImages));
    }

    /**
     * @Route("/api/client/{client}", methods={"GET"})
     */
    public function getClientProgressAction(Client $client, Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $checkIns = $this
            ->bodyProgressRepository
            ->getProgressByClient($client);

        $clientProgress = $this
            ->clientProgressService
            ->setClient($client)
            ->setProgressValues()
            ->setUnits()
            ->getProgress();

        $progressMetrics = collect($clientProgress)
            ->only(['direction', 'left', 'progress', 'percentage', 'weekly', 'last', 'start', 'goal', 'weekly', 'lastWeek', 'offText', 'progressText', 'unit'])
            ->all();

        $response = [
            'checkIns' => $checkIns,
            'metrics' => $progressMetrics
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/api/images/{client}/delete", name="progressImagesDelete")
     * @Method({"POST"})
     *
     * @param Client $client
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteImagesAction(Client $client, Request $request)
    {
        if (!$this->clientBelongsToUser($client)) {
            return new JsonResponse([], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $ids = (array) $request->request->get('ids');

        if (empty($ids)) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_MODIFIED);
        }

        $count = $this->clientImageService->remove($client, $ids);
        return new JsonResponse(['count' => $count]);
    }

    /**
     * @Route("/api/entries/{client}", name="progressEntries")
     * @Method({"GET"})
     *
     * @param Client $client
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getEntriesAction(Client $client, Request $request)
    {
        $clientProgressService = $this
            ->clientProgressService
            ->setClient($client)
            ->setUnits();

        $limit = min(max((int)$request->query->get('limit'), 5), 20);
        $offset = $request->query->get('offset');
        $type = $request->query->get('type');
        $entries = $clientProgressService->getLastEntries($limit, $offset, 'DESC', $type);

        return new JsonResponse(['data' => $entries->toArray()]);
    }

    /**
     * @Route("/api/deleteRecord/{id}/{client}", name="deleteRecord")
     *
     * @param BodyProgress $bp
     * @param Client $client
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function deleteRecordAction(BodyProgress $bp, Client $client, Request $request)
    {
        $em = $this->getEm();
        $bp
            ->setWeight(null)
            ->setFat(null)
            ->setMuscleMass(null);

        $em->flush();

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('clientProgress', array(
            'client' => $client->getId()
        ));
    }

    /**
     * @Route("/api/deleteBodyMeasurements/{bp}/{client}", name="deleteBodyMeasurements")
     *
     * @param BodyProgress $bp
     * @param Client $client
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function deleteBodyMeasurementsAction(BodyProgress $bp, Client $client, Request $request)
    {
        $em = $this->getEm();

        $bp
            ->setChest(null)
            ->setWaist(null)
            ->setGlutes(null)
            ->setHips(null)
            ->setLeftArm(null)
            ->setLeftCalf(null)
            ->setLeftThigh(null)
            ->setRightArm(null)
            ->setRightCalf(null)
            ->setRightThigh(null);

        $em->flush();

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('clientProgress', array(
            'client' => $client->getId()
        ));
    }

    /**
     * @Route("/api/addRecord/{client}", name="addRecord")
     *
     * @param Client $client
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addRecordAction(Client $client, Request $request)
    {
        if (!$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $helperService = $this->clientProgressHelperService;
        $helperService->persistProgressData($client, $helperService->getProgressParams($request));

        if ($referer = $request->headers->get('referer')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('clientProgress', [
            'client' => $client->getId()
        ]);
    }

}
