<?php

namespace ReactApiBundle\Controller\v3;

use AppBundle\Entity\Client;
use AppBundle\Entity\ProgressFeedback;
use AppBundle\Event\ClientMadeChangesEvent;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Entity\BodyProgress;
use AppBundle\Repository\BodyProgressRepository;
use AppBundle\Entity\Event;
use Stringy\Stringy as S;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use AppBundle\Repository\ClientRepository;
use AppBundle\Services\ErrorHandlerService;

/**
 * @Route("/progress")
 */
class ProgressController extends sfController
{
    private ChatService $chatService;
    private EventDispatcherInterface $eventDispatcher;
    private ErrorHandlerService $errorHandlerService;
    private BodyProgressRepository $bodyProgressRepository;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        ChatService $chatService,
        ClientRepository $clientRepository,
        ErrorHandlerService $errorHandlerService,
        BodyProgressRepository $bodyProgressRepository
    ) {
        $this->chatService = $chatService;
        $this->eventDispatcher = $eventDispatcher;
        $this->errorHandlerService = $errorHandlerService;
        $this->bodyProgressRepository = $bodyProgressRepository;

        parent::__construct($em, $clientRepository);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null,JsonResponse::HTTP_UNAUTHORIZED);
        }

        $query = $this
            ->bodyProgressRepository
            ->createQueryBuilder('bp')
            ->where('bp.client = :client')
            ->setParameter('client', $client)
            ->orderBy('bp.date', 'DESC')
            ->getQuery()->getResult();

        $data = [
            'weight' => [],
            'fat' => [],
            'circumference' => []
        ];

        foreach ($query as $k => $v) {
            /** @var $v BodyProgress */

            if ($v->getWeight()) {
                $data['weight'][] = array(
                    'id' => $v->getId(),
                    'val' => $v->getWeight() ? round($v->getWeight(), 2) : null,
                    'date' => date_format($v->getDate(), 'Y-m-d')
                );
            }
            if ($v->getFat()) {
                $data['fat'][] = array(
                    'id' => $v->getId(),
                    'val' => $v->getFat() ? round($v->getFat(), 2) : null,
                    'date' => date_format($v->getDate(), 'Y-m-d')
                );
            }

            if ($v->getChest() || $v->getWaist()
                || $v->getHips() || $v->getGlutes()
                || $v->getRightArm() || $v->getLeftArm()
                || $v->getRightThigh() || $v->getLeftThigh()
                || $v->getRightCalf() || $v->getLeftCalf()) {

                $data['circumference'][] = array(
                    'chest' => $v->getChest() ? round($v->getChest(), 2) : null,
                    'waist' => $v->getWaist() ? round($v->getWaist(), 2) : null,
                    'hips' => $v->getHips() ? round($v->getHips(), 2) : null,
                    'glutes' => $v->getGlutes() ? round($v->getGlutes(), 2) : null,
                    'left_arm' => $v->getLeftArm() ? round($v->getLeftArm(), 2) : null,
                    'right_arm' => $v->getRightArm() ? round($v->getRightArm(), 2) : null,
                    'left_thigh' => $v->getLeftThigh() ? round($v->getLeftThigh(), 2) : null,
                    'right_thigh' => $v->getRightThigh() ? round($v->getRightThigh(), 2) : null,
                    'left_calf' => $v->getLeftCalf() ? round($v->getLeftCalf(), 2) : null,
                    'right_calf' => $v->getRightCalf() ? round($v->getRightCalf(), 2) : null,
                    'date' => date_format($v->getDate(), 'Y-m-d')
                );
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $client = $this->requestClientByToken($request);

        if (!$client) {
            return new JsonResponse(null,JsonResponse::HTTP_UNAUTHORIZED);
        }

        $input = $this->requestInput($request, true);
        $em = $this->em;

        $properties = [
            'weight',
            'fat',
            'chest',
            'waist',
            'hips',
            'glutes',
            'leftArm',
            'rightArm',
            'leftThigh',
            'rightThigh',
            'leftCalf',
            'rightCalf',
        ];

        $data = [];

        foreach ($properties as $property) {
            if (isset($input[$property]) && is_numeric($input[$property])) {
                $data[$property] = $this->convertCommaToDot($input[$property]);
            }
        }

        if (count($data) === 0) {
            return new JsonResponse(null,Response::HTTP_NOT_MODIFIED);
        }

        $date = new \DateTime(isset($input['date']) ? $input['date'] : null);

        $entry = $this
            ->bodyProgressRepository
            ->getByClientAndDate($client, $date);

        if($entry == null) {
            $entry = new BodyProgress($client);
            $em->persist($entry);
        }

        try {
            $entry->setDate($date);

            foreach ($data as $field => $value) {
                $method = 'set' . S::create($field)->upperCamelize();
                call_user_func_array([$entry, $method], [$value]); /** @phpstan-ignore-line */
            }

            $client
                ->setBodyProgressUpdated(new \DateTime('now'));

            $em->flush();

            if ($client->getActive() && !$client->getDeleted()) {
                $service = $this->chatService;
                $url = $this->generateUrl('clientProgress', array('client' => $client->getId()));
                $message = "{$client->getName()} has updated his/her body progress. <a href=$url target='_blank'>Click here to review.</a>";
                $msg = $service->sendMessage($message, $client, $client->getUser(), null, true)['msg'];
            } else {
                $msg = null;
            }

            //dispatch event
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::UPDATED_BODYPROGRESS, $msg);
            $dispatcher->dispatch($event, Event::UPDATED_BODYPROGRESS);

            $data = [
                'id' => $entry->getId(),
                'chest' => $entry->getChest() ? round((float) $entry->getChest(), 2) : 0,
                'waist' => $entry->getWaist() ? round((float) $entry->getWaist(), 2) : 0,
                'hips' => $entry->getHips() ? round((float) $entry->getHips(), 2) : 0,
                'glutes' => $entry->getGlutes() ? round((float) $entry->getGlutes(), 2) : 0,
                'left_arm' => $entry->getLeftArm() ? round((float) $entry->getLeftArm(), 2) : 0,
                'right_arm' => $entry->getRightArm() ? round((float) $entry->getRightArm(), 2) : 0,
                'left_thigh' => $entry->getLeftThigh() ? round((float) $entry->getLeftThigh(), 2) : 0,
                'right_thigh' => $entry->getRightThigh() ? round((float) $entry->getRightThigh(), 2) : 0,
                'left_calf' => $entry->getLeftCalf() ? round((float) $entry->getLeftCalf(), 2) : 0,
                'right_calf' => $entry->getRightCalf() ? round((float) $entry->getRightCalf(), 2) : 0,
                'fat' => $entry->getFat() ? round((float) $entry->getFat(), 2) : null,
                'weight' =>  $entry->getWeight() ? round((float) $entry->getWeight(), 2) : null,
                'date' => date_format($entry->getDate(), 'Y-m-d')
            ];

            return new JsonResponse($data);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse(null,JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
