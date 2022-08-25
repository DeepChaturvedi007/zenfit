<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use ChatBundle\Entity\Message;
use Tests\Context\BaseWebTestCase;

class ProgressControllerTest extends BaseWebTestCase
{
    public function testCheckInAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $this->currentAuthedClientIs('client');

        //passthru('php bin/console zf:fixtures:load --env=test');

        $params = [
            'weight' => 50,
            'fat' => 20,
            'chest' => 10,
            'waist' => 11,
            'hips' => 12,
            'glutes' => 13,
            'leftArm' => 14,
            'rightArm' => 15,
            'leftThigh' => 16,
            'rightThigh' => 17,
            'leftCalf' => 18,
            'rightCalf' => 19
        ];

        $this->request('POST', '/react-api/v3/progress', $params);
        $this->responseStatusShouldBe(200);

        $this->request('GET', '/react-api/v3/progress');
        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('chest', $responseData['circumference'][0]);
        self::assertArrayHasKey('waist', $responseData['circumference'][0]);
        self::assertArrayHasKey('hips', $responseData['circumference'][0]);
        self::assertArrayHasKey('glutes', $responseData['circumference'][0]);
        self::assertArrayHasKey('left_arm', $responseData['circumference'][0]);
        self::assertArrayHasKey('right_arm', $responseData['circumference'][0]);
        self::assertArrayHasKey('left_thigh', $responseData['circumference'][0]);
        self::assertArrayHasKey('right_thigh', $responseData['circumference'][0]);
        self::assertArrayHasKey('left_calf', $responseData['circumference'][0]);
        self::assertArrayHasKey('right_calf', $responseData['circumference'][0]);
        self::assertArrayHasKey('date', $responseData['weight'][0]);
        self::assertArrayHasKey('val', $responseData['weight'][0]);
        self::assertArrayHasKey('id', $responseData['weight'][0]);
        self::assertArrayHasKey('val', $responseData['fat'][0]);

        self::assertEquals($params['weight'], $responseData['weight'][0]['val']);
        self::assertEquals($params['fat'], $responseData['fat'][0]['val']);
        self::assertEquals($params['chest'], $responseData['circumference'][0]['chest']);
        self::assertEquals($params['waist'], $responseData['circumference'][0]['waist']);
        self::assertEquals($params['hips'], $responseData['circumference'][0]['hips']);
        self::assertEquals($params['glutes'], $responseData['circumference'][0]['glutes']);
        self::assertEquals($params['leftArm'], $responseData['circumference'][0]['left_arm']);
        self::assertEquals($params['rightArm'], $responseData['circumference'][0]['right_arm']);
        self::assertEquals($params['leftThigh'], $responseData['circumference'][0]['left_thigh']);
        self::assertEquals($params['rightThigh'], $responseData['circumference'][0]['right_thigh']);
        self::assertEquals($params['leftCalf'], $responseData['circumference'][0]['left_calf']);
        self::assertEquals($params['rightCalf'], $responseData['circumference'][0]['right_calf']);
    }
}
