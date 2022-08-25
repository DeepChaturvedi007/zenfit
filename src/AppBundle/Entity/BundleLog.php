<?php declare(strict_types=1);

namespace AppBundle\Entity;

class BundleLog
{
    private ?int $id = null;
    private string $datakey;
    private int $redemptions = 0;
    private Bundle $bundle;
    private ?\DateTime $purchaseDate = null;
    private ?Client $client = null;
    private bool $confirmed = false;
    private bool $contacted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDatakey(string $datakey): self
    {
        $this->datakey = $datakey;

        return $this;
    }

    public function getDatakey(): string
    {
        return $this->datakey;
    }

    public function setRedemptions(int $redemptions): self
    {
        $this->redemptions = $redemptions;

        return $this;
    }

    public function getRedemptions(): int
    {
        return $this->redemptions;
    }

    public function setBundle(Bundle $bundle): self
    {
        $this->bundle = $bundle;

        return $this;
    }

    public function getBundle(): Bundle
    {
        return $this->bundle;
    }

    public function setPurchaseDate(?\DateTime $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTime
    {
        return $this->purchaseDate;
    }

    public function __construct(Bundle $bundle, string $datakey)
    {
        $this->bundle = $bundle;
        $this->datakey = $datakey;
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

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setContacted(bool $contacted): self
    {
        $this->contacted = $contacted;

        return $this;
    }

    public function getContacted(): bool
    {
        return $this->contacted;
    }
}
