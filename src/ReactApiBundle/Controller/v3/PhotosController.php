<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Services\ClientImageService;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Services\ErrorHandlerService;
use ReactApiBundle\Transformer\ClientImageTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\ClientImageRepository;
use League\Fractal\Manager;
use League\Fractal;

/**
 * @Route("/photos")
 */
class PhotosController extends sfController
{
    private ClientImageService $clientImageService;
    private ChatService $chatService;
    private string $s3beforeAfterImages;
    private ErrorHandlerService $errorHandlerService;
    private ClientImageRepository $clientImageRepository;

    public function __construct(
        string $s3beforeAfterImages,
        ChatService $chatService,
        ClientImageService $clientImageService,
        EntityManagerInterface $em,
        ErrorHandlerService $errorHandlerService,
        ClientRepository $clientRepository,
        ClientImageRepository $clientImageRepository
    ) {
        $this->clientImageService = $clientImageService;
        $this->chatService = $chatService;
        $this->s3beforeAfterImages = $s3beforeAfterImages;
        $this->errorHandlerService = $errorHandlerService;
        $this->clientImageRepository = $clientImageRepository;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $photos = $this
            ->clientImageRepository
            ->getClientPhotos($client);

        $baseUrl = $this->s3beforeAfterImages;
        $fractal = new Manager();
        $serializer = $fractal->setSerializer(new SimpleArraySerializer);

        $data = $serializer
            ->createData(
                new Fractal\Resource\Collection($photos, new ClientImageTransformer($baseUrl))
            )
            ->toArray();

        return new JsonResponse($data);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($request->files->count() === 0) {
            return new JsonResponse([
                'message' => 'You have to select at least one image.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $date = new \DateTime((string) $request->query->get('date'));
        } catch (\Exception $e) {
            $date = new \DateTime();
        }

        $em = $this->em;

        try {
            $baseUrl = $this->s3beforeAfterImages;
            $files = $request->files->all();

            $service = $this->clientImageService;
            foreach ($files as $type => $file) {
                $service->upload($file, $date, $client, (int) $type);
            }

            $photos = $this
                ->clientImageRepository
                ->getClientPhotos($client, $date);

            $fractal = new Manager();
            $serializer = $fractal->setSerializer(new SimpleArraySerializer);

            $data = $serializer
                ->createData(
                    new Fractal\Resource\Collection($photos, new ClientImageTransformer($baseUrl))
                )
                ->toArray();

            $em->flush();

            if ($client->getActive() && !$client->getDeleted()) {
                $service = $this->chatService;
                $url = $this->generateUrl('clientProgress', array('client' => $client->getId()));
                $msg = "{$client->getName()} has uploaded a new progress picture. <a href=$url target='_blank'>Click here to review</a>";
                $service->sendMessage($msg, $client, $client->getUser(), null, true);
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("", methods={"DELETE"})
     */
    public function deleteAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $input = $this->requestInput($request);
        $ids = isset($input->images) && is_array($input->images) ? $input->images : [];

        if (count($ids) === 0) {
            return new JsonResponse(null, JsonResponse::HTTP_NOT_MODIFIED);
        }

        $this->clientImageService->remove($client, $ids);

        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }
}
