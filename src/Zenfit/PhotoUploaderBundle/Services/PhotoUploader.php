<?php

namespace Zenfit\PhotoUploaderBundle\Services;


use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoUploader {

    private $client;
    private static $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    public function __construct(S3Client $client) {
        $this->client = $client;
    }

    /**
     * @return S3Client
     */
    public function getClient() {
        return $this->client;
    }

    public function upload(UploadedFile $file) {
        // Check if the file's mime type is in the list of allowed mime types.
        if (!in_array($file->getClientMimeType(), self::$allowedMimeTypes)) {
            throw new \InvalidArgumentException(sprintf('Files of type %s are not allowed.', $file->getClientMimeType()));
        }

        $filename = sprintf(
            "%s%s.%s",
            date("Ymd"),
            uniqid(),
            $file->getClientOriginalExtension()
        );

        $this->client->upload(
            "zenfit-exercise-images",
            $filename,
            fopen($file->getPathname(), "r+")
        );

        return $filename;
    }

}


