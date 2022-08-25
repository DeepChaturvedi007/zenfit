<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientImage;
use AppBundle\Enums\ClientImageType;
use Aws\Exception\AwsException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

if (extension_loaded('imagick')) {
    Image::configure(array('driver' => 'imagick'));
}

class ClientImageService
{
    protected EntityManagerInterface $em;
    protected AwsService $aws;
    private string $s3ImagesBucket;
    private string $s3ImagesKeyPrefix;

    public function __construct(
        EntityManagerInterface $em,
        AwsService $aws,
        string $s3ImagesBucket,
        string $s3ImagesKeyPrefix
    ) {
        $this->aws = $aws;
        $this->em = $em;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
    }

    public function upload(UploadedFile $file, DateTime $date, Client $client, int $type = 0): void
    {
        $s3 = $this->aws->getClient();
        $bucket = $this->getBucket();

        $name = static::randomKey();
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = $name . '.' . $ext;

        $image = Image::make($file)
            ->orientate()
            ->resize(1024, 1024, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg', 85);

        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $this->getObjectKey($name),
            'Body' => $image->encoded,
            'ContentType' => mime_content_type($file->getPathname())
        ]);

        $clientImage = (new ClientImage($client))
            ->setName($name)
            ->setDate($date)
            ->setType($type);

        $this->em->persist($clientImage);
        $this->em->flush();
    }

    /**
     * @param Client $client
     * @param array $ids
     *
     * @return int
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Client $client, array $ids)
    {
        $clientImages = $this->em
            ->getRepository(ClientImage::class)
            ->findBy([
                'id' => $ids,
                'client' => $client,
            ]);

        /**
         * @var ClientImage $clientImage
         */
        foreach ($clientImages as $clientImage) {
            $clientImage->setDeleted(true);
        }

        $this->em->flush();

        return count($clientImages);
    }

    /**
     * @param Client $client
     * @param int $limit
     * @param int $offset
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLast(Client $client, $limit = 10, $offset = 0)
    {
        $results = $this->em
            ->getRepository(ClientImage::class)
            ->findBy([
                'client' => $client,
                'deleted' => false
            ], [
                'date' => 'DESC',
            ], $limit, $offset);

        return collect($results);
    }

    public function getBucket(): string
    {
        return $this->s3ImagesBucket;
    }

    public function getObjectKeyPrefix(): string
    {
        return $this->s3ImagesKeyPrefix;
    }

    public function getObjectKey(string $key): string
    {
        return $this->getObjectKeyPrefix() . $key;
    }

    public static function randomKey(int $length = 25): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function getPhotoType(string $type): int
    {
        switch ($type) {
            case 'front':
                return ClientImageType::FRONT;
            case 'side':
                return ClientImageType::SIDE;
            case 'back':
                return ClientImageType::REAR;
            default:
                return 0;
        }
    }
}
