<?php declare(strict_types=1);

namespace AppBundle\Entity;

class BodyProgress
{
    private ?int $id = null;
    private ?float $weight = null;
    private ?float $muscleMass = null;
    private ?float $fat = null;
    private \DateTime $date;
    private ?float $chest = null;
    private ?float $waist = null;
    private ?float $hips = null;
    private ?float $glutes = null;
    private ?float $leftArm = null;
    private ?float $rightArm = null;
    private ?float $leftThigh = null;
    private ?float $rightThigh = null;
    private ?float $leftCalf = null;
    private ?float $rightCalf = null;
    private Client $client;

    public function __construct(Client $client)
    {
        $this->date = new \DateTime();
        $this->client = $client;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setWeight(string|float|int|null $weight): self
    {
        $this->weight = $weight === null ? null : (float) $weight;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setMuscleMass(string|float|int|null $muscleMass): self
    {
        $this->muscleMass = $muscleMass === null ? null : (float) $muscleMass;

        return $this;
    }

    public function getMuscleMass(): ?float
    {
        return $this->muscleMass;
    }

    public function setFat(string|float|int|null $fat): self
    {
        $this->fat = $fat === null ? null : (float) $fat;

        return $this;
    }

    public function getFat(): ?float
    {
        return $this->fat;
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

    public function setChest(string|float|int|null $chest): self
    {
        $this->chest = $chest === null ? null : (float) $chest;

        return $this;
    }

    public function getChest(): ?float
    {
        return $this->chest;
    }

    public function setWaist(string|float|int|null $waist): self
    {
        $this->waist = $waist === null ? null : (float) $waist;

        return $this;
    }

    public function getWaist(): ?float
    {
        return $this->waist;
    }

    public function setHips(string|float|int|null $hips): self
    {
        $this->hips = $hips === null ? null : (float) $hips;

        return $this;
    }

    public function getHips(): ?float
    {
        return $this->hips;
    }

    public function setGlutes(string|float|int|null $glutes): self
    {
        $this->glutes = $glutes === null ? null : (float) $glutes;

        return $this;
    }

    public function getGlutes(): ?float
    {
        return $this->glutes;
    }

    public function setLeftArm(string|float|int|null $leftArm): self
    {
        $this->leftArm = $leftArm === null ? null : (float) $leftArm;

        return $this;
    }

    public function getLeftArm(): ?float
    {
        return $this->leftArm;
    }

    public function setRightArm(string|float|int|null $rightArm): self
    {
        $this->rightArm = $rightArm === null ? null : (float) $rightArm;

        return $this;
    }

    public function getRightArm(): ?float
    {
        return $this->rightArm;
    }

    public function setLeftThigh(string|float|int|null $leftThigh): self
    {
        $this->leftThigh = $leftThigh === null ? null : (float) $leftThigh;

        return $this;
    }

    public function getLeftThigh(): ?float
    {
        return $this->leftThigh;
    }

    public function setRightThigh(string|float|int|null $rightThigh): self
    {
        $this->rightThigh = $rightThigh === null ? null : (float) $rightThigh;

        return $this;
    }

    public function getRightThigh(): ?float
    {
        return $this->rightThigh;
    }

    public function setLeftCalf(string|float|int|null $leftCalf): self
    {
        $this->leftCalf = $leftCalf === null ? null : (float) $leftCalf;

        return $this;
    }

    public function getLeftCalf(): ?float
    {
        return $this->leftCalf;
    }

    public function setRightCalf(string|float|int|null $rightCalf): self
    {
        $this->rightCalf = $rightCalf === null ? null : (float) $rightCalf;

        return $this;
    }

    public function getRightCalf(): ?float
    {
        return $this->rightCalf;
    }
}
