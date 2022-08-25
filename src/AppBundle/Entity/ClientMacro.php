<?php

namespace AppBundle\Entity;

/**
 * ClientMacro
 */
class ClientMacro
{
    /**
     * @var int
     */
    private $id;

    private ?float $kcal = null;
    private ?float $carbs = null;
    private ?float $protein = null;
    private ?float $fat = null;

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setKcal(string|float|int|null $value): self
    {
        if (is_int($value) || is_string($value)) {
            $value = (float) $value;
        }

        $this->kcal = $value;

        return $this;
    }

    public function getKcal(): ?float
    {
        return $this->kcal;
    }

    public function setCarbs(string|float|int|null $value): self
    {
        if (is_int($value) || is_string($value)) {
            $value = (float) $value;
        }

        $this->carbs = $value;

        return $this;
    }

    public function getCarbs(): ?float
    {
        return $this->carbs;
    }

    public function setProtein(string|float|int|null $value): self
    {
        if (is_int($value) || is_string($value)) {
            $value = (float) $value;
        }

        $this->protein = $value;

        return $this;
    }

    public function getProtein(): ?float
    {
        return $this->protein;
    }

    public function setFat(string|float|int|null $value): self
    {
        if (is_int($value) || is_string($value)) {
            $value = (float) $value;
        }

        $this->fat = $value;

        return $this;
    }

    public function getFat(): ?float
    {
        return $this->fat;
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
    /**
     * @var \DateTime
     */
    private $date;


    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return ClientMacro
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
