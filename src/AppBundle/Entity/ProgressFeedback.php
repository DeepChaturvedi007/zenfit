<?php declare(strict_types=1);

namespace AppBundle\Entity;

class ProgressFeedback
{
    private ?int $id = null;
    private Client $client;
    private string $content;
    private \DateTime $createdAt;

    public function __construct(Client $client, string $content)
    {
        $this->client = $client;
        $this->content = $content;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
