<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotFoundEventListener
{
    private UrlGeneratorInterface $router;
    private TokenStorageInterface $tokenStorage;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $route = null;
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $route = 'clients';
        }

        if ($event->getThrowable() instanceof AccessDeniedHttpException
            && !$event->getRequest()->isXmlHttpRequest()
            && !str_contains($event->getRequest()->getRequestUri(), '/api/')
        ) {
            $user = $this->tokenStorage->getToken()?->getUser();
            if (!$user instanceof User) {
                $route = 'authLogin';
            }
        }

        if ($route !== null) {
            if ($route === $event->getRequest()->get('_route')) {
                return;
            }

            $url = $this->router->generate($route, ['_target_path' => $event->getRequest()->getRequestUri()]);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}
