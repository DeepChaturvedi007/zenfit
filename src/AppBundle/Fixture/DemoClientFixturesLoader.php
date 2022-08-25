<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Client;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSettings;
use AppBundle\Entity\UserSubscription;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\SubscriptionRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserSettingsRepository;
use AppBundle\Repository\UserSubscriptionRepository;

class DemoClientFixturesLoader
{
    private ClientRepository $clientRepository;
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;
    private UserSubscriptionRepository $userSubscriptionRepository;
    private UserSettingsRepository $userSettingsRepository;

    public function __construct(
        ClientRepository $clientRepository,
        SubscriptionRepository $subscriptionRepository,
        UserSettingsRepository $userSettingsRepository,
        UserSubscriptionRepository $userSubscriptionRepository,
        UserRepository $userRepository
    ) {
        $this->clientRepository = $clientRepository;
        $this->userRepository = $userRepository;
        $this->userSettingsRepository = $userSettingsRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userSubscriptionRepository = $userSubscriptionRepository;
    }

    public function __invoke(): void
    {
        $subscription = $this->subscriptionRepository->findOneBy(['title' => 'None']);
        $templateUser = $this->userRepository->findOneBy(['email' => 'template@zenfitapp.com']);
        if ($templateUser === null) {
            $templateUser = new User();

            $templateUser->setUsername('Template');
            $templateUser->setEmail('template@zenfitapp.com');
            $templateUser->setEnabled(true);
            $templateUser->setPassword(uniqid());
            $templateUser->setName('Template');
            $templateUser->setFirstName('Template');
            $templateUser->setActivated(true);

            $this->userRepository->persist($templateUser);
            $this->userRepository->flush();
        }

        $userSettings = $this->userSettingsRepository->findOneBy(['user' => $templateUser]);
        if ($userSettings === null) {
            $userSettings = new UserSettings($templateUser);
            $userSettings->setReceiveEmailOnNewMessage(true);
            $templateUser->setUserSettings($userSettings);

            $this->userSettingsRepository->persist($userSettings);
            $this->userSettingsRepository->flush();
        }

        $userSubscription = $this->userSubscriptionRepository->findOneBy(['user' => $templateUser]);
        if ($userSubscription === null) {
            $userSubscription = new UserSubscription($templateUser);
            $userSubscription->setSubscription($subscription);
            $templateUser->setUserSubscription($userSubscription);

            $this->userSubscriptionRepository->persist($userSubscription);
            $this->userSubscriptionRepository->flush();
        }

        $demoClient = $this->clientRepository->findOneBy([
            'lasseDemoClient' => 1
        ]);

        if ($demoClient === null) {
            $demoClient = new Client($templateUser, 'Thor Odinsson', 'thor@zenfitapp.com');
            $demoClient->setPassword(uniqid());
            $demoClient->setPhone('+1 1234-567-890');
            $demoClient->setInjuries('Not really. A little back pain in lower back sometimes (Sit down alot)');
            $demoClient->setExperience('Used to play football for 5 years. Now I workout 3-4 times per week.');
            $demoClient->setOther('Can only workout mon-friday. Want bigger pecs. more v-shape.');
            $demoClient->setAge(30);
            $demoClient->setExercisePreferences("I want to do fitness. I love weight lifting. and body building. I don't like cardio on treadmill etc.");
            $demoClient->setStartWeight(272);
            $demoClient->setHeight(195.6);
            $demoClient->setLifestyle('I work as a consultant. I sit down most of the day.');
            $demoClient->setGender(Client::GENDER_MALE);
            $demoClient->setPrimaryGoal(2);
            $demoClient->setMotivation('8-9 out of 10. I am REALLY motivated to loose weight!');
            $demoClient->setActive(true);
            $demoClient->setStartDate(new \DateTime('2018-02-12'));
            $demoClient->setDayTrackProgress(4);
            $demoClient->setAnsweredQuestionnaire(false);
            $demoClient->setDuration(3);
            $demoClient->setPhoto('bff447665b9a6568437f987b880d5cef.jpg');
            $demoClient->setMeasuringSystem(Client::MEASURING_SYSTEM_IMPERIAL);
            $demoClient->setActivityLevel(4);
            $demoClient->setGoalWeight(240);
            $demoClient->setDietStyle('Lots of meats and carbs.');
            $demoClient->setBodyProgressUpdated(new \DateTime('2019-07-22 10:28:27'));
            $demoClient->setEndDate(new \DateTime());
            $demoClient->setAccessApp(true);
            $demoClient->setDemoClient(true);
            $demoClient->setLasseDemoClient(true);

            $this->clientRepository->persist($demoClient);
            $this->clientRepository->flush();
        }
    }
}
