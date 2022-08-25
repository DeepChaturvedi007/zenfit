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

class FakeClientsFixturesLoader
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
        $templateUser = $this->userRepository->findOneBy(['email' => 'tumsemm@gmail.com']);
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

        $i = 0;
        do {
            $client = new Client($templateUser, uniqid(), 'thor'.uniqid().'@zenfitapp.com');
            $client->setPassword(uniqid());
            $client->setPhone('+1 1234-567-890');
            $client->setInjuries('Not really. A little back pain in lower back sometimes (Sit down alot)');
            $client->setExperience('Used to play football for 5 years. Now I workout 3-4 times per week.');
            $client->setOther('Can only workout mon-friday. Want bigger pecs. more v-shape.');
            $client->setDemoClient(true);
            $client->setLasseDemoClient(true);
            $client->setAge(30);
            $client->setExercisePreferences("I want to do fitness. I love weight lifting. and body building. I don't like cardio on treadmill etc.");
            $client->setStartWeight(272);
            $client->setHeight((float)195.6);
            $client->setLifestyle('I work as a consultant. I sit down most of the day.');
            $client->setGender(Client::GENDER_MALE);
            $client->setPrimaryGoal(2);
            $client->setMotivation('8-9 out of 10. I am REALLY motivated to loose weight!');
            $client->setActive(true);
            $client->setStartDate(new \DateTime('2018-02-12'));
            $client->setDayTrackProgress(4);
            $client->setAnsweredQuestionnaire(false);
            $client->setDuration(3);
            $client->setPhoto('bff447665b9a6568437f987b880d5cef.jpg');
            $client->setMeasuringSystem(Client::MEASURING_SYSTEM_IMPERIAL);
            $client->setActivityLevel(4);
            $client->setGoalWeight(240);
            $client->setDietStyle('Lots of meats and carbs.');
            $client->setBodyProgressUpdated(new \DateTime('2019-07-22 10:28:27'));
            $client->setEndDate(new \DateTime());
            $client->setAccessApp(true);
            $i++;

            $this->clientRepository->persist($client);
        } while ($i <= 500);

        $this->clientRepository->persist($client);
        $this->clientRepository->flush();
    }
}
