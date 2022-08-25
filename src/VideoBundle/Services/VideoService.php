<?php declare(strict_types=1);

namespace VideoBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Video;
use AppBundle\Entity\VideoTag;
use AppBundle\Entity\Client;
use AppBundle\Entity\VideoClient;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use AppBundle\Services\PushNotificationService;
use AppBundle\PushMessages\PushNotificationServiceException;
use Symfony\Contracts\Translation\TranslatorInterface;

class VideoDuplicateException extends \Exception {}

class ValidateException extends \Exception {}

class VideoService
{
    private EntityManagerInterface $em;
    private PushNotificationService $pushNotificationService;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        PushNotificationService $pushNotificationService,
        TranslatorInterface $translator
    )
    {
        $this->em = $em;
        $this->pushNotificationService = $pushNotificationService;
        $this->translator = $translator;
    }

    public function setVideoTags(Video $video, string $tagsString): self
    {
        $tags = explode(',', $tagsString);
        $currentTags = array_map(function($tag) {
            return $tag['title'];
        }, $video->tagsList());

        $diff = array_diff($tags, $currentTags);

        foreach ($diff as $t) {
            $existingTag = $this->em
                ->getRepository(VideoTag::class)
                ->findOneBy([
                    'video' => $video,
                    'title' => $t
                ]);

            if ($existingTag) {
                $this->em->remove($existingTag);
            } else {
                //tag does not exist and should be added
                $videoTag = new VideoTag($video, $t);
                $this->em->persist($videoTag);
            }
        }

        $this->em->flush();
        return $this;
    }

    public function updateOrCreate(User $user, ?int $id, string $title, string $url, string $picture, string $comment, int $assignWhen, string $assignToString): Video
    {
        $video = null;
        if($id) {
            /** @var VideoRepository $videoRepository */
            $videoRepository = $this->em->getRepository(Video::class);
            $video = $videoRepository->find($id);
        }

        if(!$video) {
            $video = new Video($user);
            $video->setCreatedAt(new \DateTime());
        } elseif ($video->getUrl() !== $url) {
            $this->validateUniqueness($video->getUser(), $url);
        }

        $assignmentTags = array_filter(explode(',', $assignToString));

        $this->validateData([
            'title' => $title,
            'url' => $url,
            'picture' => $picture,
            'comment' => $comment,
            'assignWhen' => $assignWhen,
            'assignmentTags' => $assignmentTags,
            'user' => $user,
        ]);

        $video
            ->setUser($user)
            ->setTitle($title)
            ->setComment($comment)
            ->setUrl($url)
            ->setAssignWhen($assignWhen)
            ->setPicture($picture);

        if(in_array('all', $assignmentTags) || in_array('#all', $assignmentTags)) {
            $video->setAssignmentTags(['#all']);
        } else {
            $video->setAssignmentTags($assignmentTags);
        }

        $this->em->persist($video);
        $this->em->flush();

        return $video;
    }

    /**
     * @param Video $video
     * @param Client $client
     * @throws ORMException
     * @throws OptimisticLockException
     * @return VideoClient
     */
    public function createVideoClientEntity(Video $video, Client $client): VideoClient
    {
        $videoClient = new VideoClient($video, $client);

        $this->em->persist($videoClient);
        return $videoClient;
    }

    public function assignVideoToClients(Video $video, bool $pushNotification = false): Video
    {
        $em = $this->em;
        $existingClients = $this
            ->em
            ->getRepository(VideoClient::class)
            ->findBy([
                'video' => $video
            ]);

        $collection = collect($existingClients);

        if ($video->getAssignWhen() === 0) {
            //delete unlocked rows
            $collection
                ->filter(function (VideoClient $vc) {
                    return !$vc->getLocked();
                })->map(function($item) use ($em) {
                    $em->remove($item);
                });

            $existingClientVideoIds = $collection
                ->filter(function (VideoClient $vc) {
                    return $vc->getLocked();
                })->map(function($item) {
                    return $item->getClient()->getId();
                });
        } else {
            $existingClientVideoIds = $collection
                ->map(function($item) {
                    return $item->getClient()->getId();
                });
        }


        $assignmentTags = $video->getAssignmentTags();

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->em->getRepository(Client::class);
        $clients = $clientRepo->getClientsByFilters($video->getUser(), true, null, null, null, [], $assignmentTags, false, null, 'ASC', $video->getAssignWhen());
        //add video to client with tag
        foreach ($clients as $client) {
            if (in_array($client->getId(), $existingClientVideoIds->toArray())) continue;
            $this->createVideoClientEntity($video, $client);

            //if we should send a push notification to client
            if ($pushNotification) {
                if (!method_exists($this->translator, 'setLocale')) {
                    throw new \RuntimeException();
                }
                $this->translator->setLocale($client->getLocale());
                try {
                    $content = $this->translator->trans('client.notifications.newVideo', [
                        '%name%' => $client->getFirstName()
                    ]);

                    $this
                        ->pushNotificationService
                        ->sendToClient($client, $content, [], [], null, 'VideoLibraryScreen');
                } catch (PushNotificationServiceException $e) {}
            }
        }

        $this->em->flush();

        return $video;
    }

    public function getAssignmentTags(array $videos): array
    {
        return collect($videos)
            ->map(function(Video $video) {
                if (empty($video->getAssignmentTags())) {
                    return ['#all'];
                }
                return $video->getAssignmentTags();
            })
            ->flatten()
            ->sort()
            ->countBy()
            ->toArray();
    }

    /**
     * @param mixed $data
     * @throws ValidateException
     */
    private function validateData($data)
    {
        if(!$data['title'] || !is_string($data['title']) || empty($data['title'])) {
            throw new ValidateException('Title is required for video entity');
        }
        if(!$data['url'] || !is_string($data['url']) || empty($data['url'])) {
            throw new ValidateException('Url is required for video entity');
        }
        if(!$data['picture'] || !is_string($data['picture'])) {
            throw new ValidateException('Picture is required for video entity');
        }
        if(!$data['assignmentTags'] || empty($data['assignmentTags'])) {
            throw new ValidateException('You need to specify whom to assign the video to.');
        }
        if(!$data['user'] || ! ($data['user'] instanceof User)) {
            throw new ValidateException('User (owner) is required for video entity');
        }
    }

    /**
     * @param User $user
     * @param string $url
     * @throws VideoDuplicateException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function validateUniqueness(User $user, $url)
    {
        // Check for existence similar video for the user
        $videoRepository = $this->em->getRepository(Video::class);
        // Prevent creation duplicates
        if($videoRepository->isUserAlreadyHaveVideo($user, $url)) {
            throw new VideoDuplicateException("You've already added video \"{$url}\"");
        }
    }
}
