<?php declare(strict_types=1);

namespace Tests\ReactApiBundle\Controller\v3;

use AppBundle\Entity\ClientImage;
use Tests\Context\BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotosControllerTest extends BaseWebTestCase
{
    public function testGetAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $client = $this->getOrCreateDummyClient('client', $user);

        $this->currentAuthedClientIs('client');

        $file = new UploadedFile($this->application->getKernel()->getProjectDir() . '/tests/test.png', 'test.png');

        $this->request('POST', '/react-api/v3/photos', [], true, [$file]);
        $this->responseStatusShouldBe(200);

        $this->request('GET', '/react-api/v3/photos');
        $this->responseStatusShouldBe(200);

        $responseData = $this->getResponseArray();
        self::assertArrayHasKey('id', $responseData[0]);
        self::assertArrayHasKey('name', $responseData[0]);
        self::assertArrayHasKey('date', $responseData[0]);
        self::assertArrayHasKey('uri', $responseData[0]);
    }
}
