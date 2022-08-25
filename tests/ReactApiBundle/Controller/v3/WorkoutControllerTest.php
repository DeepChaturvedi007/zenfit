<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use AppBundle\Entity\WorkoutPlan;
use Tests\Context\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WorkoutControllerTest extends BaseWebTestCase
{
    public function testGetAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $this->currentAuthedUserIs('user');

        $workoutPlan = $this
            ->em
            ->getRepository(WorkoutPlan::class)
            ->find(1);

        self::assertInstanceOf(WorkoutPlan::class, $workoutPlan);

        $this->request('POST', "/api/workout/client/assign-plan/{$workoutPlan->getId()}", [
            'clientsIds' => [$client->getId()]
        ]);
        $this->responseStatusShouldBe(200);

        //get client data
        $this->currentAuthedClientIs('client');
        $this->request('GET', '/react-api/v3/workout');
        $this->responseStatusShouldBe(200);
        $clientResponseData = $this->getResponseArray();

        self::assertArrayHasKey('id', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('name', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('explaination', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('comment', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('last_updated', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('created', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('days', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('status', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('active', $clientResponseData['plans'][0]);
        self::assertArrayHasKey('meta', $clientResponseData['plans'][0]);

        self::assertEquals($workoutPlan->getName(), $clientResponseData['plans'][0]['name']);
    }
}
