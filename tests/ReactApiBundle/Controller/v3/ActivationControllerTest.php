<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use AppBundle\Entity\Client;
use AppBundle\Entity\Queue;
use Tests\Context\BaseWebTestCase;

class ActivationControllerTest extends BaseWebTestCase
{
    public function testPostClientInfio(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);
        $client->setMeasuringSystem(Client::MEASURING_SYSTEM_IMPERIAL);
        $client->setStartWeight(123);
        $this->em->flush();

        $phone = uniqid();
        $name = uniqid();
        $email = uniqid().'@gmail.com';

        //create new queue entity
        $datakey = uniqid();
        $queue = new Queue($email, $name, Queue::STATUS_SENDGRID_DELIVERED, Queue::TYPE_CLIENT_EMAIL);
        $queue->setDatakey($datakey);
        $this->em->persist($queue);
        $this->em->flush();

        $measureSystem = 1;
        $startWeight = 75;
        $excludeIngredients = uniqid();
        $clientFoodPreferences = [
            'avoidShellfish',
            'avoidPig',
        ];
        $tags = ['a', 'b', 'c'];

        $this->request('POST', "/react-api/v3/activation/submit", [
            'measuringSystem' => $measureSystem,
            'startWeight' => $startWeight,
            'phone' => $phone,
            'email' => $email,
            'name' => $name,
            'tags' => $tags,
            'clientFoodPreferences' => $clientFoodPreferences,
            'excludeIngredients' => $excludeIngredients,
            'datakey' => $datakey,
            'client' => $client->getId(),
            'isQuestionnaire' => true
        ]);

        $this->responseStatusShouldBe(200);

        $response = $this->getResponseArray();

        $expected = [
            "id" => $client->getId(),
            "name" => $name,
            "firstName" => $client->getFirstName(),
            "info" => [
                "phone" => $phone,
                "height" => null,
                "feet" => null,
                "inches" => null,
                "injuries" => null,
                "experience" => null,
                "experienceLevel" => null,
                "other" => null,
                "age" => null,
                "locale" => "en",
                "lifestyle" => null,
                "gender" => null,
                "motivation" => null,
                "activityLevel" => null,
                "dietStyle" => null,
                "workoutsPerWeek" => null,
                "numberOfMeals" => null,
                "workoutLocation" => null,
                "updateWorkoutSchedule" => 4,
                "updateMealSchedule" => 4,
                "measuringSystem" => 1,
                "goalType" => 1,
                "goalWeight" => null,
                "startWeight" => $startWeight,
                "clientFoodPreferences" => [],
                "exercisePreferences" => null,
                "pal" => null,
                "notes" => [
                    "note" => null,
                    "salesNotes" => null,
                    "dialogMessage" => null,
                ],
                "id" => $client->getId(),
                "email" => $email,
                "name" => $name,
                "photo" => null,
                "isActive" => true,
            ],
            "email" => $email,
            "photo" => null,
            "active" => true,
            "demoClient" => false,
            "createdAt" => (array) $client->getCreatedAt(),
            "startDate" => null,
            "duration" => null,
            "endDate" => null,
            "tags" => $tags,
            "status" => [],
            "reminders" => [],
            "payments" => [],
            "measuringSystem" => $measureSystem,
            "goalWeight" => null,
            "bodyProgressUpdated" => null,
            "queue" => null,
            "trainer" => [
                "id" => $client->getUser()->getId(),
                "name" => $client->getUser()->getName(),
            ],
        ];

        self::assertEquals($expected, $response);

        $this->em->refresh($client);

        $clientFoodPreferences = $client->getClientFoodPreferences();
        self::assertNotNull($clientFoodPreferences);
        self::assertEquals($excludeIngredients, $clientFoodPreferences->getExcludeIngredients());
        self::assertTrue($clientFoodPreferences->getAvoidPig());
        self::assertTrue($clientFoodPreferences->getAvoidShellfish());
        self::assertFalse($clientFoodPreferences->getAvoidEggs());
        self::assertFalse($clientFoodPreferences->getAvoidFish());
        self::assertFalse($clientFoodPreferences->getAvoidGluten());
        self::assertFalse($clientFoodPreferences->getAvoidLactose());
        self::assertFalse($clientFoodPreferences->getAvoidNuts());
    }
}
