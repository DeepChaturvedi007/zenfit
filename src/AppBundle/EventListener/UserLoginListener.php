<?php declare(strict_types=1);

namespace AppBundle\EventListener;

use AppBundle\Entity\ActivityLog;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Event\TrainerMadeChangesEvent;

class UserLoginListener
{
    private EntityManagerInterface $em;
    private SessionInterface $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function onUserLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException();
        }

        if (!$user->getInteractiveToken()) {
            $token = md5(uniqid($user->getEmail(), true));
            $user->setInteractiveToken($token);
        }

        $this->session->getFlashBag()->add('login', 1);

        $now = new \DateTime('now');
        $eventEntity = $this->em->getRepository(Event::class)->findOneBy([
            'name' => TrainerMadeChangesEvent::LOGIN
        ]);
        if ($eventEntity === null) {
            throw new \RuntimeException('TrainerMadeChangesEvent is not in DB');
        }

        $activityLog = $this->em
            ->getRepository(ActivityLog::class)
            ->getByEventAndDate(null, $user, $eventEntity, $now);

        if (!$activityLog) {
            $activityLog = new ActivityLog();

            $activityLog
                ->setEvent($eventEntity)
                ->setUser($user);
        } else {
            $activityLog->setCount($activityLog->getCount() + 1);
        }

        $activityLog->setDate($now);
        $this->em->persist($activityLog);
        $this->em->flush();
    }
}
