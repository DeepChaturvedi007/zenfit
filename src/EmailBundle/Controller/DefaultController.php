<?php

namespace EmailBundle\Controller;

use AppBundle\Entity\Queue;
use AppBundle\Entity\Event;
use AppBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/email")
 */
class DefaultController extends Controller
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/callback")
     */
    public function indexAction(Request $request): Response
    {
        $em = $this->getEm();

        $content = $request->getContent();
        $post = collect(json_decode($content, true))[0];
        $event = $post['event'];
        $queueId = $post['queue'] ?? null;
        $isQueue = isset($queueId) ? true : false;

        if($isQueue) {
            $item = $em->getRepository(Queue::class)->find((int) $queueId);

            if($item) {

                $client = $item->getClient();

                if($event == 'deferred') {
                    $status = Queue::STATUS_SENDGRID_DEFERRED;
                } else if($event == 'delivered') {
                    $status = Queue::STATUS_SENDGRID_DELIVERED;
                } else if($event == 'dropped') {
                    $status = Queue::STATUS_SENDGRID_DROPPED;
                } else if($event == 'bounce') {
                    $status = Queue::STATUS_SENDGRID_BOUNCED;

                    if($client) {
                        $dispatcher = $this->eventDispatcher;
                        $event = new ClientMadeChangesEvent($client, Event::WRONG_EMAIL);
                        $dispatcher->dispatch($event, Event::WRONG_EMAIL);
                    }

                } else if($event == 'click') {
                    $status = Queue::STATUS_SENDGRID_CLICKED;
                } else if($event == 'open') {
                    $status = Queue::STATUS_SENDGRID_OPENED;
                } else {
                    $status = Queue::STATUS_SENDGRID_UNKNOWN;
                }

                $item->setStatus($status);
            }

            $em->flush();
        }

        return new Response('OK');
    }
}
