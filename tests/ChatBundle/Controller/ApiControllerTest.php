<?php declare(strict_types=1);

namespace Tests\ChatBundle\Controller;

use ChatBundle\Entity\Message;
use Tests\Context\BaseWebTestCase;

class ApiControllerTest extends BaseWebTestCase
{
    public function testSendTextMessage(): void
    {
        $trainer = $this->getOrCreateDummyUser('trainer');
        $client = $this->getOrCreateDummyClient('client', $trainer);

        $this->currentAuthedUserIs('trainer');

        $message = uniqid();
        $this->request('POST', '/chat/api/send', [
            'msg' => $message,
            'clientId' => $client->getId(),
            'trainer' => $trainer->getId(),
        ], false);

        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();
        self::assertArrayHasKey('messages', $responseData);
        self::assertIsArray($responseData['messages']);
        self::assertArrayHasKey(0, $responseData['messages']);
        self::assertArrayHasKey('content', $responseData['messages'][0]);
        self::assertEquals($message, $responseData['messages'][0]['content']);
        self::assertEquals($client->getId(), $responseData['messages'][0]['clientId']);
        self::assertEquals($trainer->getId(), $responseData['messages'][0]['trainer']);
    }

    public function testSendVideoMessage(): void
    {
        $trainer = $this->getOrCreateDummyUser('trainer');
        $client = $this->getOrCreateDummyClient('client', $trainer);

        $this->currentAuthedUserIs('trainer');

        $video = 'before-after-images/trainers/video-messages/Cr8QlrQyxKZUGkqytQcfdYLgDu98kwLL.webm';
        $this->request('POST', '/chat/api/send', [
            'media' => $video,
            'clientId' => $client->getId(),
            'trainer' => $trainer->getId(),
        ], false);

        $this->responseStatusShouldBe(200);

        $this->iConsumeAllMessagesInQueue('video_compress', 1);
        $this->iConsumeAllMessagesInQueue('voice_compress', 0);

        $messages = $this->messageRepository->findAll();
        self::assertArrayHasKey(0, $messages);

        self::assertInstanceOf(Message::class, $messages[0]);
        //TODO also check if media convert job has been created with correct settings

        $this->responseStatusShouldBe(200);
    }

    public function testSendVoiceMessage(): void
    {
        $trainer = $this->getOrCreateDummyUser('trainer');
        $client = $this->getOrCreateDummyClient('client', $trainer);

        $this->currentAuthedUserIs('trainer');

        $voice = 'before-after-images/trainers/video-messages/xXrrRuBdbWHwGUIXOMpXqJtPKkLxWsjO.wav';
        $this->request('POST', '/chat/api/send', [
            'media' => $voice,
            'clientId' => $client->getId(),
            'trainer' => $trainer->getId(),
        ], false);

        $this->responseStatusShouldBe(200);

        $this->iConsumeAllMessagesInQueue('voice_compress', 1);
        $this->iConsumeAllMessagesInQueue('video_compress', 0);

        $messages = $this->messageRepository->findAll();
        self::assertArrayHasKey(0, $messages);

        self::assertInstanceOf(Message::class, $messages[0]);
        //TODO also check if media convert job has been created with correct settings

        $this->responseStatusShouldBe(200);
    }

    public function testSendMessageDifferentTrainer(): void
    {
        $trainer = $this->getOrCreateDummyUser('trainer');
        $this->getOrCreateDummyUser('some-other-trainer');
        $client = $this->getOrCreateDummyClient('client', $trainer);

        $this->currentAuthedUserIs('some-other-trainer');

        $this->request('POST', '/chat/api/send', [
            'msg' => uniqid(),
            'clientId' => $client->getId(),
            'trainer' => $trainer->getId(),
        ], false);

        $this->responseStatusShouldBe(403);
    }
}
