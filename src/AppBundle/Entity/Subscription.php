<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Subscription
{
    public const COUNTRY_DK = 'dk';
    public const COUNTRY_EU = 'eu';
    public const COUNTRY_NON_EU = 'non-eu';

    private ?int $id = null;
    private string $title;
    private string $country;
    private int $priceMonth = 0;
    private ?string $slug = null;
    private ?int $tax = null;
    private ?string $stripeNameMonth = null;
    private bool $tiered = false;
    private string $currency;
    private ?string $upfrontFee = null;
    private bool $growth = false;

    /** @var Collection<int, User>*/
    private Collection $users;
    /** @var Collection<int, UserSubscription> */
    private Collection $userSubscription;

    public function __construct(string $title, string $country, int $priceMonth, string $slug, string $stripeNameMonth, string $currency, int $tax)
    {
        $this->title = $title;
        $this->priceMonth = $priceMonth;
        $this->slug = $slug;
        $this->stripeNameMonth = $stripeNameMonth;
        $this->tax = $tax;
        $this->currency = $currency;
        $this->setCountry($country);
        $this->users = new ArrayCollection();
        $this->userSubscription = new ArrayCollection();
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        if (!in_array($country, [self::COUNTRY_DK, self::COUNTRY_EU, self::COUNTRY_NON_EU], true)) {
            throw new \RuntimeException('unknown country type');
        }

        $this->country = $country;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setPriceMonth(int $priceMonth): self
    {
        $this->priceMonth = $priceMonth;

        return $this;
    }

    public function getPriceMonth(): int
    {
        return $this->priceMonth;
    }

    public function setStripeNameMonth(?string $stripeNameMonth): self
    {
        $this->stripeNameMonth = $stripeNameMonth;

        return $this;
    }

    public function getStripeNameMonth(): ?string
    {
        return $this->stripeNameMonth;
    }

    /** @return Collection<int, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setSlug(?string $slug = null): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setTax(?int $tax = null): self
    {
        $this->tax = $tax;
        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTiered(bool $tiered): self
    {
        $this->tiered = $tiered;
        return $this;
    }

    public function getTiered(): bool
    {
        return $this->tiered;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setGrowth(bool $growth): self
    {
        $this->growth = $growth;
        return $this;
    }

    public function getGrowth(): bool
    {
        return $this->growth;
    }

    public function setUpfrontFee(?string $upfrontFee): self
    {
        $this->upfrontFee = $upfrontFee;
        return $this;
    }

    public function getUpfrontFee(): ?string
    {
        return $this->upfrontFee;
    }

    /** @return Collection<int, UserSubscription> */
    public function getUserSubscription(): Collection
    {
        return $this->userSubscription;
    }
}
