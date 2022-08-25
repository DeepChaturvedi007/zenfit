<?php

namespace AppBundle\Services;

use Aws\Sdk;
use Illuminate\Support\Str;

class AwsService
{
    protected Sdk $client;
    private string $s3ImagesKeyPrefix;
    private string $awsKey;
    private string $awsSecret;

    public function __construct(string $s3ImagesKeyPrefix, string $awsKey, string $awsSecret)
    {
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->awsKey = $awsKey;
        $this->awsSecret = $awsSecret;

        $this->client = new Sdk([
            'region' => 'eu-central-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $awsKey,
                'secret' => $awsSecret
            ]
        ]);
    }

    public function getClient(): \Aws\S3\S3Client
    {
        return $this->client->createS3();
    }

    public function createPresignedRequest(string $bucket, string $key, ?string $contentType = null): string
    {
        $cmd = $this->getClient()
            ->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $key,
                'ContentType' => $contentType
            ]);

        return (string) $this
            ->getClient()
            ->createPresignedRequest($cmd, '+30 minutes')
            ->getUri();
    }

    public function generateKey(?string $prefix = null, string $ext = 'mp4'): string
    {
        $s3Prefix = $this->s3ImagesKeyPrefix;
        $key = Str::random(32);

        if ($prefix) {
            $key = $prefix . '/' . $key;
        }

        return $s3Prefix . $key . '.' . $ext;
    }


}
