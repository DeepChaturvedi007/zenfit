<?php declare(strict_types=1);

namespace AppBundle\Entity;

use ChatBundle\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ClientStatus
{
    private ?int $id = null;
    private \DateTime $date;
    private Client $client;
    private Event $event;
    /** @var Collection<int, Message> */
    private Collection $messages;
    private ?\DateTime $resolvedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
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

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    private bool $resolved = false;

    public function setResolved(bool $resolved): self
    {
        $this->resolved = $resolved;

        return $this;
    }

    public function getResolved(): bool
    {
        return $this->resolved;
    }

    /** @return Collection<int, Message> */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function __construct(Event $event, Client $client)
    {
        $this->event = $event;
        $this->client = $client;
        $this->messages = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function setResolvedBy(?\DateTime $resolvedBy): self
    {
        $this->resolvedBy = $resolvedBy;

        return $this;
    }

    public function getResolvedBy(): ?\DateTime
    {
        return $this->resolvedBy;
    }
}
