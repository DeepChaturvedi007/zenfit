<?php

namespace AppBundle\Entity;

/**
 * PushMessage
 */
class PushMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var \DateTime|null
     */
    private $sentAt;

    /**
     * @var \AppBundle\Entity\Client
     */
    private $client;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message.
     *
     * @param string|null $message
     *
     * @return PushMessage
     */
    public function setMessage($message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set sentAt.
     *
     * @param \DateTime|null $sentAt
     *
     * @return PushMessage
     */
    public function setSentAt($sentAt = null)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt.
     *
     * @return \DateTime|null
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set client.
     *
     * @param \AppBundle\Entity\Client|null $client
     *
     * @return PushMessage
     */
    public function setClient(\AppBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \AppBundle\Entity\Client|null
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * @var bool
     */
    private $delivered = false;


    /**
     * Set delivered.
     *
     * @param bool $delivered
     *
     * @return PushMessage
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;

        return $this;
    }

    /**
     * Get delivered.
     *
     * @return bool
     */
    public function getDelivered()
    {
        return $this->delivered;
    }
}
