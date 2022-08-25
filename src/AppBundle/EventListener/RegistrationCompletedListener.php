<?php declare(strict_types=1);

namespace AppBundle\EventListener;

use AppBundle\Entity\Document;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSettings;
use AppBundle\Entity\UserSubscription;
use AppBundle\Services\DemoClientService;
use AppBundle\Services\MealPlanService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegistrationCompletedListener implements EventSubscriberInterface
{
    public const EVENT_NAME = 'registration_completed';

    public function __construct(
        private MealPlanService $mealPlanService,
        private DemoClientService $demoClientService,
        private SessionInterface $session,
        private EntityManagerInterface $em,
    ) { }

    /** {@inheritDoc} */
    public static function getSubscribedEvents(): array
    {
        return [
            self::EVENT_NAME => 'onRegistrationCompleted'
        ];
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        $subscription = null;
        $cookies = $event->getRequest()->cookies;

        if ($cookies->has('sub')) {
            $subscription = $this->em->getRepository(Subscription::class)->findOneBy([
                'slug' => (string) $cookies->get('sub')
            ]);
        }

        if ($subscription === null) {
            $subscription = $this->em->getRepository(Subscription::class)->find(1);
        }

        if ($subscription !== null) {
            $userSubscription = $user->getUserSubscription();

            if (!$userSubscription) {
                $userSubscription = (new UserSubscription($user));

                $this->em->persist($userSubscription);
            }

            $userSubscription->setSubscription($subscription);
        }

        $name = $user->getName();
        if ($name === '') {
            $user->setName($user->getUsername());
        }

        $interactiveToken = $user->getInteractiveToken();
        if ($interactiveToken === null) {
            $token = md5(uniqid($user->getEmail(), true));
            $user->setInteractiveToken($token);
        }

        $userSettings = $user->getUserSettings();
        if ($userSettings === null) {
            $userSettings = new UserSettings($user);
            $this->em->persist($userSettings);
        }

        $this->session->getFlashBag()->add('signup', 1);

        $this->createUserDemoDocuments($user);
        $this->createUserDemoMealTemplates($user);
        $this->demoClientService->createDemoClient($user);
        $this->em->flush();
    }

    private function createUserDemoDocuments(User $user)
    {
        $documents = $this->em->getRepository(Document::class)->findBy([
            'demo' => 1,
            'user' => null
        ]);

        foreach ($documents as $document) {
            $newDocument = clone $document;
            $newDocument->setUser($user);
            $this->em->persist($newDocument);
        }
    }

    private function createUserDemoMealTemplates(User $user)
    {
        $service = $this->mealPlanService;
        $plans = $this->em->getRepository(MasterMealPlan::class)->findBy([
            'demo' => 1,
            'user' => null,
            'template' => 1
        ]);

        foreach ($plans as $plan) {
            $service->createMasterPlan($plan->getName(), $plan->getExplaination(), $user, null, $plan);
        }
    }
}
