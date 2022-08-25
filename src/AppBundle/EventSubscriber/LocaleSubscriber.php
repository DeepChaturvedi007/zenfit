<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Queue;
use AppBundle\Services\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $defaultLocale,
        private TranslationService $translationService,
        private EntityManagerInterface $em
    ) {}

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!preg_match('/(clientActivation|clientSurvey|clientDownloadApp|checkout)/i', $request->getPathInfo())) {
            return;
        }

        $locale = null;

        if ($request->query->has('locale')) {
            $locale = $request->query->get('locale');
            $request->getSession()->set('_locale', $locale);
            $request->attributes->set('_locale', $locale);
        } else if ($request->request->has('locale')) {
            $locale = $request->request->get('locale');
            $request->getSession()->set('_locale', $locale);
            $request->attributes->set('_locale', $locale);
        }

        $locale = $locale ?? $request->getSession()->get('_locale');

        if($locale) {
            $request->setLocale($locale);
        } else {
            $locale = $this->translationService->detectByCountry($this->defaultLocale);
            $request->setLocale($request->getSession()->get('_locale', $locale));
        }

        // update client's locale to locale of browser
        if ($request->query->has('datakey')) {
            $datakey = $request->query->get('datakey');
            $repo = $this->em->getRepository(Queue::class);
            $queue = $repo->findOneByDatakey($datakey);
            if($queue && $queue->getClient() && $queue->getClient()->getLocale() != $locale) {
                $client = $queue->getClient();
                $client->setLocale($locale);
                $this->em->flush();
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
