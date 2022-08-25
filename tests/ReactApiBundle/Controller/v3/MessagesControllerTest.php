<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use ChatBundle\Entity\Message;
use Tests\Context\BaseWebTestCase;

class MessagesControllerTest extends BaseWebTestCase
{
    public function testSendMessageAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $content = uniqid();
        $this->request('POST', '/react-api/v3/messages', [
            'content' => $content
        ], true, [], ['HTTP_Authorization' => $client->getToken()]); /* @phpstan-ignore-line */

        $this->responseStatusShouldBe(200);

        $message = $this
            ->em
            ->getRepository(Message::class)
            ->findOneBy(['content' => $content]);

        self::assertInstanceOf(Message::class, $message);

        $this->request('GET', '/react-api/v3/messages', [], true, [], ['HTTP_Authorization' => $client->getToken()]); /* @phpstan-ignore-line */
        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('id', $responseData[0]);
        self::assertArrayHasKey('content', $responseData[0]);
        self::assertEquals($content, $responseData[0]['content']);
        self::assertArrayHasKey('date', $responseData[0]);
        self::assertArrayHasKey('client', $responseData[0]);
        self::assertArrayHasKey('trainer', $responseData[0]);
        self::assertEquals($user->getId(), $responseData[0]['trainer']);
        self::assertArrayHasKey('unseen', $responseData[0]);
        self::assertArrayHasKey('clientImg', $responseData[0]);
        self::assertArrayHasKey('isUpdate', $responseData[0]);
        self::assertArrayHasKey('video', $responseData[0]);
        self::assertArrayHasKey('status', $responseData[0]);
        self::assertArrayHasKey('clientId', $responseData[0]);
        self::assertEquals($client->getId(), $responseData[0]['clientId']);
        self::assertArrayHasKey('clientStatus', $responseData[0]);

        $this->request('GET', '/react-api/v3/messages');
        $this->responseStatusShouldBe(401);
    }
}
