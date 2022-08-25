<?php declare(strict_types=1);

namespace Tests\GymBundle\Controller\v1;

use Tests\Context\BaseWebTestCase;
use GymBundle\Entity\Gym;

class ApiControllerTest extends BaseWebTestCase
{
    public function testCreateTrainer(): void
    {
        $name = uniqid();
        $email = uniqid().'@email.com';
        $password = uniqid();

        $user = $this->getOrCreateDummyUser('user');
        $gym = new Gym($user);
        $gym->setName(uniqid());
        $this->em->persist($gym);

        $this->currentAuthedUserIs('user', false, true);
        $this->request('POST', '/gym/v1/api', [
            'admin' => $user->getInteractiveToken(),
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        $this->responseStatusShouldBe(200);

        $newUser = $this->userRepository->findOneBy(['email' => $email]);

        self::assertNotNull($newUser);
        self::assertEquals($name, $newUser->getName());

        $this->request('GET', '/gym/v1/api');
        $this->responseStatusShouldBe(200);
        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('id', $responseData[0]);
        self::assertArrayHasKey('email', $responseData[0]);
        self::assertArrayHasKey('name', $responseData[0]);

        self::assertEquals($email, $responseData[0]['email']);
        self::assertEquals($name, $responseData[0]['name']);
    }
}
