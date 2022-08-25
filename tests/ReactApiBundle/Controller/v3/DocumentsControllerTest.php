<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use AppBundle\Entity\Document;
use Tests\Context\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentsControllerTest extends BaseWebTestCase
{
    public function testGetAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $this->currentAuthedUserIs('user');

        $file = new UploadedFile($this->application->getKernel()->getProjectDir() . '/tests/test.pdf', 'test.pdf');

        $title = uniqid();
        $this->request('POST', '/docs/api/upload', [
            'title' => $title,
            'comment' => 'comment'
        ], false, [$file]);
        $this->responseStatusShouldBe(200);

        $document = $this->em->getRepository(Document::class)->findOneBy(['name' => $title]);

        self::assertInstanceOf(Document::class, $document);

        $this->request('POST', "/docs/api/{$client->getId()}/{$document->getId()}", [], false);
        $this->responseStatusShouldBe(200);

        $this->request('GET', '/react-api/v3/documents', [], true, [], ['HTTP_Authorization' => $client->getToken()]); /* @phpstan-ignore-line */
        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('id', $responseData[0]);
        self::assertArrayHasKey('name', $responseData[0]);
        self::assertArrayHasKey('comment', $responseData[0]);
        self::assertArrayHasKey('url', $responseData[0]);
        self::assertArrayHasKey('image', $responseData[0]);

        $this->request('GET', '/react-api/v3/documents');
        $this->responseStatusShouldBe(401);
    }
}
