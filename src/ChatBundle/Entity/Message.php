<?php declare(strict_types=1);

namespace ChatBundle\Entity;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientStatus;
use AppBundle\Entity\User;

class Message
{
    private ?int $id = null;
    private ?string $content = null;
    private Conversation $conversation;
    private bool $feedbackGiven = false;
    private ?string $status = null;
    private ?User $user = null;
    private ?Client $client = null;
    private \DateTime $sentAt;
    private bool $deleted = false;
    private ?ClientStatus $clientStatus = null;
    private ?string $video = null;
    private bool $isNew = false;
    private bool $isProgress = false;

    public function __construct(Conversation $conversation, \DateTime $sentAt)
    {
        $this->conversation = $conversation;
        $this->sentAt = $sentAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setSentAt(\DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    /**
     * Set isNew
     *
     * @param boolean $isNew
     *
     * @return Message
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set isNew
     *
     * @param boolean $isProgress
     *
     * @return Message
     */
    public function setIsProgress($isProgress)
    {
        $this->isProgress = $isProgress;

        return $this;
    }
    /**
     * Get isProgress
     *
     * @return boolean
     */
    public function getIsProgress()
    {
        return $this->isProgress;
    }

    /**
     * @return bool
     */
    public function getFeedbackGiven()
    {
        return $this->feedbackGiven;
    }

    /**
     * @param bool $feedbackGiven
     *
     * @return Message
     */
    public function setFeedbackGiven($feedbackGiven)
    {
        $this->feedbackGiven = $feedbackGiven;
        return $this;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setClientStatus(?ClientStatus $clientStatus): self
    {
        $this->clientStatus = $clientStatus;

        return $this;
    }

    public function getClientStatus(): ?ClientStatus
    {
        return $this->clientStatus;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return Message
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
}
