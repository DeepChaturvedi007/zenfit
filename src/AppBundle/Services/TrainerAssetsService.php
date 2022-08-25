<?php declare(strict_types=1);

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

if (extension_loaded('imagick')) {
    Image::configure(array('driver' => 'imagick'));
}

class TrainerAssetsService
{
    protected EntityManagerInterface $em;
    protected AwsService $aws;
    private string $youtubeApiKey;
    private string $s3ImagesBucket;
    private string $s3rootUrl;
    private string $s3ImagesKeyPrefix;
    private string $rootDir;

    public function __construct(
        EntityManagerInterface $em,
        AwsService $aws,
        string $youtubeApiKey,
        string $s3ImagesBucket,
        string $s3rootUrl,
        string $s3ImagesKeyPrefix,
        string $rootDir
    ) {
        $this->youtubeApiKey = $youtubeApiKey;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->s3rootUrl = $s3rootUrl;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->rootDir = $rootDir;
        $this->aws = $aws;
        $this->em = $em;
    }

    public function uploadProfilePicture(UploadedFile $file, User $user): void
    {
        $s3 = $this->aws->getClient();
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = 'picture/' . md5($user->getId() . $user->getEmail()) . '.' . $ext;

        $image = Image::make($file)
            ->orientate()
            ->fit(256, 256, function ($constraint) {
                $constraint->upsize();
            })
            ->encode('jpg', 85);

        $s3->putObject([
            'Bucket' => $this->getBucket(),
            'Key' => $this->getObjectKey($name),
            'Body' => $image->encoded,
            'ContentType' => mime_content_type($file->getPathname())
        ]);

        $url = $this->getS3Url() . $this->getObjectKey($name);
        $this->getUserSettings($user)->setProfilePicture($url);
        $this->em->flush();
    }

    public function uploadCompanyLogo(UploadedFile $file, User $user): void
    {
        $s3 = $this->aws->getClient();
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = 'company-logo/' . md5($user->getId() . $user->getEmail()) . '.' . $ext;

        $image = Image::make($file)
            ->orientate()
            ->resize(256, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode(null, 85);

        $s3->putObject([
            'Bucket' => $this->getBucket(),
            'Key' => $this->getObjectKey($name),
            'Body' => $image->encoded,
            'ContentType' => mime_content_type($file->getPathname())
        ]);

        $url = $this->getS3Url() . $this->getObjectKey($name);
        $this->getUserSettings($user)->setCompanyLogo($url);
        $this->em->flush();
    }

    public function getUserSettings(User $user): UserSettings
    {
        $userSettings = $user->getUserSettings();
        if ($userSettings === null) {
            $userSettings = new UserSettings($user);
            $user->setUserSettings($userSettings);
            $this->em->persist($userSettings);
            $this->em->flush();

            return $userSettings;
        }

        return $userSettings;
    }

    public function getYoutubeId(string $video): ?string
    {
        if (str_contains($video, '/shorts/')) {
            preg_match('/.+\/shorts\/([^"&?\/ ]{11})/', $video, $matches);
        } else {
            preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $video, $matches);
        }

        return $matches[1] ?? null;
    }

    private function checkIfVimeoVideoIsValid(string $video): bool
    {
        $headers = @get_headers($video);
        if ($headers === false) {
            return false;
        }

        return strpos($headers[0], '200') > 0;
    }

    public function getUserIntroVideo(User $user): ?string
    {
        $video = $this
            ->getUserSettings($user)
            ->getVideo();

        if ($video) {
            if (str_contains($video, 'yout')) {
                try {
                    $youtubeId = $this->getYoutubeId($video);
                    if ($youtubeId !== null) {
                        if (!$this->checkIfYouTubeVideoIsValid($youtubeId)) {
                            throw new HttpException(422, 'YouTube video invalid.');
                        }
                    } else {
                        $video = null;
                    }
                } catch (\Exception) {
                    $video = null;
                }
            } elseif (str_contains($video, 'vimeo')) {
                if (!$this->checkIfVimeoVideoIsValid($video)) {
                    throw new HttpException(422, 'Vimeo video invalid.');
                }
            } else {
                throw new HttpException(422, 'Video is invalid. Please contact Zenfit Support.');
            }
        }

        return $video;
    }

    private function checkIfYouTubeVideoIsValid(string $youtubeId): bool
    {
        $apiKey = $this->youtubeApiKey;
        $result = @file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=status&id=$youtubeId&key=$apiKey");

        if(!$result) {
            return false;
        }

        $result = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        if (isset($result['error']) || !isset($result['items']) || count($result['items']) === 0) {
            return false;
        }

        return true;
    }

    public function getBucket(): string
    {
        return $this->s3ImagesBucket;
    }

    public function getS3Url(): string
    {
        return $this->s3rootUrl;
    }

    public function getObjectKeyPrefix(): string
    {
        return $this->s3ImagesKeyPrefix;
    }

    public function getObjectKey(string $key): string
    {
        return $this->getObjectKeyPrefix() . 'trainers/' . $key;
    }

    public function getUserTerms(User $user): string
    {
        if ($userTerms = $user->getUserTerms()) {
            return $userTerms->getTerms();
        }

        $terms = file_get_contents($this->rootDir . '/web/terms.txt');

        if ($terms !== false) {
            if ($companyName = $this->getUserSettings($user)->getCompanyName()) {
                $terms = str_replace("%company-name%", $companyName, $terms);
            } else {
                $terms = str_replace("%company-name%", $user->getName(), $terms);
            }

            return $terms;
        }

        return '';
    }
}
