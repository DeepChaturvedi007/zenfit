<?php declare(strict_types=1);

namespace AppBundle\Entity;

class ClientTag
{
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct(private Client $client, private string $title)
    {
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
