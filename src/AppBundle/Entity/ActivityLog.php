<?php

namespace AppBundle\Entity;

/**
 * ActivityLog
 */
class ActivityLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;
    private ?int $notifiedTrainer = null;
    private ?User $user = null;
    private ?Client $client = null;

    /**
     * @var bool
     */
    private $seen = false;

    /**
     * @var int
     */
    private $count = 1;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ActivityLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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

    public function setNotifiedTrainer(?int $notifiedTrainer): self
    {
        $this->notifiedTrainer = $notifiedTrainer;

        return $this;
    }

    public function getNotifiedTrainer(): ?int
    {
        return $this->notifiedTrainer;
    }

    /**
     * @var \AppBundle\Entity\Event
     */
    private $event;


    /**
     * Set event
     *
     *
     * @return ActivityLog
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function isSeen()
    {
        return $this->seen;
    }

    /**
     * @param bool $seen
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;
    }

    /**
     * Get seen
     *
     * @return boolean
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }
}
