<?php declare(strict_types=1);

namespace Tests\ClientBundle\Controller;

use AppBundle\Entity\BodyProgress;
use AppBundle\Entity\Client;
use Tests\Context\BaseWebTestCase;

class ApiControllerTest extends BaseWebTestCase
{
    public function testSubmitClientInfo(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);
        $client->setMeasuringSystem(Client::MEASURING_SYSTEM_IMPERIAL);
        $client->setStartWeight(123);
        $this->em->flush();

        $measureSystem = 1;
        $startWeight = 75;
        $phone = uniqid();
        $email = uniqid().'@gmail.com';
        $excludeIngredients = uniqid();
        $clientFoodPreferences = [
            'avoidShellfish',
            'avoidPig',
        ];
        $tags = ['a', 'b', 'c'];

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            'measuringSystem' => $measureSystem,
            'startWeight' => $startWeight,
            'phone' => $phone,
            'email' => $email,
            'tags' => $tags,
            'clientFoodPreferences' => $clientFoodPreferences,
            'excludeIngredients' => $excludeIngredients,
        ]);

        $this->responseStatusShouldBe(200);

        $response = $this->getResponseArray();

        $expected = [
            "id" => $client->getId(),
            "name" => $client->getName(),
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
                "name" => $client->getName(),
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

    public function testSubmitClientInfoNonArrayClientFoodPreferences(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            'measuringSystem' => 1,
            'startWeight' => 75,
            'tags' => [],
            'clientFoodPreferences' => uniqid(),
            'excludeIngredients' => uniqid(),

        ]);

        $this->responseStatusShouldBe(422);

        $response = $this->getResponseArray();

        self::assertArrayHasKey('message', $response);
        self::assertEquals('Bad clientFoodPreferences field provided', $response['message']);
    }

    public function testSubmitClientInfoNoClientFoodPreferences(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            'measuringSystem' => 1,
            'startWeight' => 75,
            'tags' => [],

        ]);

        $this->responseStatusShouldBe(200);
    }

    public function testSubmitClientInfoWrongMeasuringSystem(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            'measuringSystem' => 3,
        ]);

        $this->responseStatusShouldBe(422);

        $response = $this->getResponseArray();

        self::assertArrayHasKey('message', $response);
        self::assertEquals('Wrong measuring system', $response['message']);
    }

    public function testSubmitClientInfoWrongStartWeight(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client', $user);

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            'measuringSystem' => 1,
        ]);

        $this->responseStatusShouldBe(422);

        $response = $this->getResponseArray();

        self::assertArrayHasKey('message', $response);
        self::assertEquals('Wrong start weight', $response['message']);
    }

    public function testSubmitClientInfoSomeoneElseClient(): void
    {
        $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');

        $client = $this->getOrCreateDummyClient('client');

        $this->request('POST', "/api/client/submitClientInfo/{$client->getId()}", [
            uniqid() => uniqid(),
        ]);

        $this->responseStatusShouldBe(403);
    }

    public function testAddClientAction(): void
    {
        $clientName = uniqid();
        $clientEmail = uniqid().'@email.com';

        $user = $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user');
        $this->request('POST', '/api/client/add', [
            'clientName' => $clientName,
            'clientEmail' => $clientEmail,
            'tags' => [],
        ]);

        $this->responseStatusShouldBe(200);

        $client = $this->clientRepository->findOneBy(['email' => $clientEmail]);

        self::assertInstanceOf(Client::class, $client);
        self::assertEquals($clientName, $client->getName());
        self::assertEquals($user, $client->getUser());
    }
}
