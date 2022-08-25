<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use Tests\Context\BaseWebTestCase;

class MealsControllerTest extends BaseWebTestCase
{
    public function testGenerateMealPlanAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $this->currentAuthedUserIs('user');

        passthru('php bin/console zf:fixtures:load --env=test');

        $title = uniqid();
        $params = [
            'name' => $title,
            'alternatives' => 1,
            'numberOfMeals' => 3,
            'type' => 1,
            'desiredKcals' => 2500,
            'macros' => [
                'carbohydrate' => '',
                'protein' => '',
                'fat' => ''
            ],
            'macroSplit' => 2,
            'avoid' => [],
            'prioritize' => false,
            'locale' => 'da_DK',
            'excludeIngredients' => []
        ];

        $this->request('POST', "/api/v3/meal/plans/generate/{$client->getId()}", $params);
        $this->responseStatusShouldBe(200);
        $trainerResponseData = $this->getResponseArray();
        self::assertArrayHasKey('plan', $trainerResponseData);

        //get client data
        $this->currentAuthedClientIs('client');

        $this->request('GET', '/react-api/v3/meals');
        $this->responseStatusShouldBe(200);
        $clientResponseData = $this->getResponseArray();

        self::assertArrayHasKey('id', $clientResponseData[0]);
        self::assertArrayHasKey('name', $clientResponseData[0]);

        self::assertEquals($trainerResponseData['plan'], $clientResponseData[0]['id']);
        self::assertEquals($title, $clientResponseData[0]['name']);
    }
}
