<?php declare(strict_types=1);

namespace AppBundle\Entity;

class VideoClient
{
    private ?int $id = null;
    private Video $video;
    private Client $client;
    private bool $locked = false;
    private bool $deleted = false;
    private bool $isNew = true;

    public function __construct(Video $video, Client $client)
    {
        $this->video = $video;
        $this->client = $client;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;
        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }
}
