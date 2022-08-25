<?php declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JWTRefreshedListener implements EventSubscriberInterface
{
    public function __construct(private SessionInterface $session) { }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        /** @var string $newAccessToken */
        $newAccessToken = $this->session->get('newAccessToken');
        if ($newAccessToken !== null) {
            $response->headers->setCookie(Cookie::create('BEARER', $newAccessToken));
            $this->session->remove('newAccessToken');
        }
    }

    /** @return array<mixed> */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
