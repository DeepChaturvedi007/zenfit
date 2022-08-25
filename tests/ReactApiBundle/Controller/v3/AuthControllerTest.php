<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use Tests\Context\BaseWebTestCase;

class AuthControllerTest extends BaseWebTestCase
{
    public function testLoginAction(): void
    {
        $clientEmail = uniqid().'@email.com';
        $trainerEmail = uniqid().'@email.com';
        $password = uniqid();

        $trainer = $this->getOrCreateDummyUser('trainer', $trainerEmail);
        $this->getOrCreateDummyClient('client', $trainer, $clientEmail, $password);

        $this->request('POST', '/react-api/v3/auth/login', [
            'email' => $clientEmail,
            'password' => $password,
        ]);

        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('email', $responseData);
        self::assertEquals($clientEmail, $responseData['email']);
        self::assertArrayHasKey('trainer', $responseData);
        self::assertArrayHasKey('email', $responseData['trainer']);
        self::assertEquals($trainerEmail, $responseData['trainer']['email']);


        $this->request('POST', '/react-api/v3/auth/login', [
            'email' => $clientEmail,
            'password' => uniqid(),
        ]);

        $this->responseStatusShouldBe(422);
    }
}
