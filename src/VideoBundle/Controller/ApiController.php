<?php

namespace VideoBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\VideoClient;
use AppBundle\Security\CurrentUserFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Video;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use VideoBundle\Services\ValidateException;
use VideoBundle\Services\VideoDuplicateException;
use VideoBundle\Services\VideoService;
use AppBundle\Repository\VideoRepository;
use AppBundle\Repository\VideoClientRepository;
use AppBundle\Repository\ClientRepository;
use VideoBundle\Transformer\VideoTransformer;
use VideoBundle\Transformer\VideoClientTransformer;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    public function __construct(
        private VideoService $videoService,
        private EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private VideoRepository $videoRepository,
        private ClientRepository $clientRepository,
        private VideoClientRepository $videoClientRepository,
        private VideoTransformer $videoTransformer,
        private VideoClientTransformer $videoClientTransformer,
        private CurrentUserFetcher $currentUserFetcher,
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getVideos(Request $request): JsonResponse
    {
        $currentUser = $this->currentUserFetcher->getCurrentUser();
        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $videos = $this
            ->videoRepository
            ->findByUser($user);

        $serialized = collect($videos)->map(function(Video $video) {
            return $this->videoTransformer->transform($video);
        });

        return new JsonResponse($serialized->toArray());
    }

    /**
     * @Route("/{client}", methods={"GET"})
     */
    public function getClientVideos(Request $request, Client $client): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException("You don't have access to this client");
        }

        $videos = $this
            ->videoClientRepository
            ->findByClient($client);

        return new JsonResponse($videos);
    }

    /**
     * @Route("/{client}/{video}", methods={"POST"})
     */
    public function addVidToClient(Request $request, Client $client, Video $video): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $videoClient = (new VideoClient($video, $client))
                ->setLocked(true);

            $this->em->persist($videoClient);
            $this->em->flush();
            return new JsonResponse($this->videoClientTransformer->transform($videoClient));
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("/{client}/{video}", methods={"DELETE"})
     */
    public function deleteVideoClient(Video $video, Client $client, Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();

        if (!$this->clientBelongsToUser($client, $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $em = $this->getEm();
            $videoClient = $em->getRepository(VideoClient::class)
                ->findOneBy([
                    'video' => $video,
                    'client' => $client
                ]);

            if ($videoClient === null) {
                throw new NotFoundHttpException();
            }
            $em->remove($videoClient);
            $em->flush();
            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([], 422);
        }
    }

    /**
     * @Route("/", name="update_or_create_video", methods={"POST"})
     */
    public function updateOrCreate(Request $request): JsonResponse
    {
        $user               = $this->getUser();
        $id                 = (int) $request->request->get('id', null);
        $title              = (string) $request->request->get('title', '');
        $url                = (string) $request->request->get('url', '');
        $picture            = (string) $request->request->get('picture', '');
        $comment            = (string) $request->request->get('description', '');
        $videoTagsString    = (string) $request->request->get('hashtags', '');
        $assignToString     = (string) $request->request->get('assign', '');
        $assignWhen         = (int) $request->request->get('assignWhen', 0);

        try {
            if ($user === null) {
                throw new AccessDeniedHttpException('Please login');
            }

            $video = $this
                ->videoService
                ->updateOrCreate($user, $id, $title, $url, $picture, $comment, $assignWhen, $assignToString);

            $this->videoService->setVideoTags($video, $videoTagsString);

            //video should be assigned immediately
            if ($assignWhen === 0) {
                $this
                    ->videoService
                    ->assignVideoToClients($video);
            }

            return new JsonResponse($video);
        } catch (VideoDuplicateException $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_CONFLICT);
        } catch (ValidateException $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(
     *     "/{video}",
     *     name="delete_video",
     *     methods={"DELETE"}
     * )
     * @param Request $request
     * @param Video $video
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, Video $video)
    {
        $shouldDeleteEverywhere = $request->query->getBoolean('everywhere', false);
        $em = $this->getEm();
        if($shouldDeleteEverywhere) {
            /** @var VideoClient[] $videoClients */
            $videoClients = $video->getVideoClients();
            foreach ($videoClients as $videoClient) {
                $em->remove($videoClient);
            }
        }
        $video->setDeleted(true);
        $em->persist($video);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route(
     *     "/{video}/play",
     *     name="play_video",
     *     methods={"GET"}
     * )
     * @param Video $video
     * @return Response
     */
    public function play(Video $video)
    {
        $data = array(
            'id' => $video->getId(),
            'name' => $video->getTitle(),
            'video' => $video->getUrl(),
        );

        return $this->render('@App/default/exerciseInfo.html.twig', array(
            'exercise' => $data
        ));
    }

    /**
     * @Route("/add-video-to-client", name="add_video_to_client", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addVideoToClient(Request $request): Response
    {
        $title = $request->request->get('title');
        $url = $request->request->get('url');
        $client = $this
            ->clientRepository
            ->find($request->request->get('client'));

        if (!$client instanceof Client) {
            throw new NotFoundHttpException('Client not found');
        }

        if (!$this->clientBelongsToUser($client)) {
            throw new AccessDeniedHttpException();
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $video = new Video($user);
        $video
            ->setTitle($title)
            ->setUrl($url)
            ->setCreatedAt(new \DateTime('now'))
            ->setDeleted(true);

        $this->em->persist($video);
        $this->videoService->createVideoClientEntity($video, $client);
        $this->em->flush();

        return $this->redirectToRoute('clientVideos', ['client' => $client->getId()]);
    }
}
