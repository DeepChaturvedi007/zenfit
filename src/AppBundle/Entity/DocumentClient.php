<?php declare(strict_types=1);

namespace AppBundle\Entity;

class DocumentClient
{
    private ?int $id = null;
    private Document $document;
    private Client $client;
    private bool $locked = false;

    public function __construct(Document $document, Client $client)
    {
        $this->document = $document;
        $this->client = $client;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): Document
    {
        return $this->document;
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
}
