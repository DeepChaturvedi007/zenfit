<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Entity\ClientImage;
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
use League\Fractal\Manager;
use League\Fractal;
use Imagick;

/**
 * @Route("/v2/photos")
 */
class PhotosController extends sfController
{
    private ClientImageService $clientImageService;
    private ChatService $chatService;
    private string $s3beforeAfterImages;
    private ErrorHandlerService $errorHandlerService;

    public function __construct(
        string $s3beforeAfterImages,
        ChatService $chatService,
        ClientImageService $clientImageService,
        EntityManagerInterface $em,
        ErrorHandlerService $errorHandlerService,
        ClientRepository $clientRepository
    ) {
        $this->clientImageService = $clientImageService;
        $this->chatService = $chatService;
        $this->s3beforeAfterImages = $s3beforeAfterImages;
        $this->errorHandlerService = $errorHandlerService;

        parent::__construct($em, $clientRepository);
    }
    /**
     * @Method({"GET"})
     * @Route("")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $photos = $this
            ->em
            ->getRepository(ClientImage::class)
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
     * @Method({"POST"})
     * @Route("")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $client = $this->requestClient($request);

        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($request->files->count() === 0) {
            return new JsonResponse([
                'message' => 'You have to select at least one image.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $date = new \DateTime($request->query->get('date'));
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

            $photos = $em
                ->getRepository(ClientImage::class)
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

            return new JsonResponse($data, JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Method({"DELETE"})
     * @Route("")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $client = $this->requestClient($request);

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

    /**
     * @param $imgUrl
     * @return Imagick
     * @throws \ImagickException
     */
    private function autoRotatedImage($imgUrl)
    {
        $image = new Imagick($imgUrl);
        $orientation = $image->getImageOrientation();

        match ($orientation) {
            Imagick::ORIENTATION_BOTTOMRIGHT => $image->rotateimage('#000', 180),
            Imagick::ORIENTATION_RIGHTTOP => $image->rotateimage('#000', 90),
            Imagick::ORIENTATION_LEFTBOTTOM => $image->rotateimage('#000', -90),
            default => null,
        };

        $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);

        return $image;
    }

}
