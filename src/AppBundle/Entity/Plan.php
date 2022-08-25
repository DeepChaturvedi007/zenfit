<?php

namespace AppBundle\Entity;

/**
 * Plan
 */
class Plan
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $type = 0;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var bool
     */
    private $contacted = false;

    /**
     * @var \DateTime|null
     */
    private $createdAt;

    /**
     * @var \AppBundle\Entity\Bundle
     */
    private $bundle;

    /**
     * @var bool
     */
    private bool $deleted = false;

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
     * Set type.
     *
     * @param int $type
     *
     * @return Plan
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return Plan
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
     * Set contacted.
     *
     * @param bool $contacted
     *
     * @return Plan
     */
    public function setContacted($contacted)
    {
        $this->contacted = $contacted;

        return $this;
    }

    /**
     * Get contacted.
     *
     * @return bool
     */
    public function getContacted()
    {
        return $this->contacted;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime|null $createdAt
     *
     * @return Plan
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

    public function setDeleted(bool $value): self
    {
        $this->deleted = $value;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Set bundle.
     *
     * @param \AppBundle\Entity\Bundle|null $bundle
     *
     * @return Plan
     */
    public function setBundle(\AppBundle\Entity\Bundle $bundle = null)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * Get bundle.
     *
     * @return \AppBundle\Entity\Bundle|null
     */
    public function getBundle()
    {
        return $this->bundle;
    }
    /**
     * @var \AppBundle\Entity\Client
     */
    private $client;


    /**
     * Set client.
     *
     * @param \AppBundle\Entity\Client|null $client
     *
     * @return Plan
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
     * @var \AppBundle\Entity\Payment
     */
    private $payment;


    /**
     * Set payment.
     *
     * @param \AppBundle\Entity\Payment|null $payment
     *
     * @return Plan
     */
    public function setPayment(\AppBundle\Entity\Payment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment.
     *
     * @return \AppBundle\Entity\Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
