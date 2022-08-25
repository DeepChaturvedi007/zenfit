<?php

namespace AppBundle\Entity;

use App\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Video
 */
class Video
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $comment;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->client = new ArrayCollection();
        $this->videoTags = new ArrayCollection();
        $this->videoClients = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function tagsList()
    {
        return array_map(function(VideoTag $tag) {
            return [
                'id' => $tag->getId(),
                'title' => $tag->getTitle()
            ];
        }, $this->getVideoTags());
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return Video
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return Video
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return Video
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @var string|null
     */
    private $picture;


    /**
     * Set picture.
     *
     * @param string|null $picture
     *
     * @return Video
     */
    public function setPicture($picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture.
     *
     * @return string|null
     */
    public function getPicture()
    {
        return $this->picture;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $client;

    /**
     * Add client.
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return Video
     */
    public function addClient(\AppBundle\Entity\Client $client)
    {
        $this->client[] = $client;

        return $this;
    }

    /**
     * Remove client.
     *
     * @param \AppBundle\Entity\Client $client
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeClient(\AppBundle\Entity\Client $client)
    {
        return $this->client->removeElement($client);
    }

    /**
     * Get client.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * @var bool
     */
    private $deleted = false;


    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return Video
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    private ?string $assignmentTags = null;


    /**
     * Set assignmentTags.
     *
     * @param array $assignmentTags
     *
     * @return Video
     */
    public function setAssignmentTags(array $assignmentTags = [])
    {
        $data = ['tags' => $assignmentTags];
        $this->assignmentTags = json_encode($data, JSON_THROW_ON_ERROR);
        return $this;
    }

    public function getAssignmentTags(): array
    {
        if ($this->assignmentTags === null) {
            return [];
        }
        $data = json_decode($this->assignmentTags, true, 512, JSON_THROW_ON_ERROR);
        if (!$data) {
            return [];
        }

        //if tags contains '#all'
        //return empty array, indicating that all clients should receive this video
        $all = false;
        foreach ($data['tags'] as $tag) {
            if ($tag === '#all') {
                $all = true;
            }
        }

        if ($all) {
            return [];
        }

        $tagsArray = is_array($data['tags']) ? $data['tags'] : [];
        return array_filter($tagsArray);
    }

    /**
     * @return bool
     */
    public function isAssignedToAll()
    {
        $tags = $this->getAssignmentTags();
        return empty($tags);
    }

    /** @var Collection<int, VideoTag> */
    private Collection $videoTags;

    /** @return array<VideoTag> */
    public function getVideoTags(): array
    {
        return $this->videoTags->toArray();
    }
    /**
     * @var \DateTime|null
     */
    private $createdAt;


    /**
     * Set createdAt.
     *
     * @param \DateTime|null $createdAt
     *
     * @return Video
     */
    public function setCreatedAt($createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'comment' => $this->getComment(),
            'picture' => $this->getPicture(),
            'assign' => $this->getAssignmentTags(),
            'hashtags' => $this->tagsList(),
            'assignWhen' => $this->getAssignWhen()
        ];
    }
    /** @var Collection<int, VideoClient> */
    private Collection $videoClients;

    /** @return VideoClient[] */
    public function getVideoClients(): array
    {
        return $this->videoClients->toArray();
    }

    private int $assignWhen = 0;

    public function setAssignWhen(int $assignWhen): self
    {
        $this->assignWhen = $assignWhen;

        return $this;
    }

    public function getAssignWhen(): int
    {
        return $this->assignWhen;
    }
}
