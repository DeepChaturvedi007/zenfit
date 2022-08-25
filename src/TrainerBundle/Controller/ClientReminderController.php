<?php

namespace TrainerBundle\Controller;

use AppBundle\Entity\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\Controller;
use AppBundle\Entity\ClientReminder;
use AppBundle\Transformer\ClientReminderTransformer;

/**
 * @Route("/api/client-reminder")
 */
class ClientReminderController extends Controller
{
    /**
     * @Route("/resolve", name="resolveClientReminder", methods={"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function resolveAction(Request $request)
    {
        // return new JsonREsponse($request->get('id'));
        $em = $this->getEm();
        $repo = $em->getRepository(ClientReminder::class);
        $clientReminder = $repo->find($request->get('id'));
        if(!$clientReminder) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No clientReminder entity was found.'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clientReminder->setResolved(true);
        $em->flush();

        return new JsonResponse('OK');
    }

    /**
     * @Route("", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function postClientReminderAction(Request $request)
    {
        try {
            $client = $this
                ->getEm()
                ->getRepository(Client::class)
                ->find($request->get('client'));

            $dueDate = $request->get('dueDate');
            $title = $request->get('title');

            $cm = (new ClientReminder())
                ->setClient($client)
                ->setDueDate(new \DateTime($dueDate))
                ->setTitle($title);

            $this->getEm()->persist($cm);
            $this->getEm()->flush();

            return new JsonResponse((new ClientReminderTransformer())->transform($cm));

        } catch (\Exception $e) {
            return new JsonResponse([
                'err' => $e->getMessage()
            ], 422);
        }
    }
}
