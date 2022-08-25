<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class PaymentsLog
{
    use EntityIdTrait;

    const PAYMENT_FAILED = 'invoice.payment_failed';
    const PAYMENT_SUCCEEDED = 'invoice.payment_succeeded';
    const CHARGE_SUCCEEDED = 'charge.succeeded';
    const CHARGE_REFUNDED = 'charge.refunded';
    const SUBSCRIPTION_CANCELED = 'customer.subscription.deleted';
    const SUBSCRIPTION_PAYMENT_WAITING = 'customer.subscription.payment_waiting';

    private string $type;
    private ?\DateTime $createdAt = null;
    private ?\DateTime $arrivalDate = null;
    private ?Client $client = null;
    private ?StripeConnect $stripeConnect = null;
    private ?User $user = null;
    private ?string $customer = null;
    private ?string $charge = null;
    private ?string $amount = null;
    private ?string $currency = null;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(?string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getCharge(): ?string
    {
        return $this->charge;
    }

    public function setCharge(?string $charge): self
    {
        $this->charge = $charge;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client = null): self
    {
        $this->client = $client;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user = null): self
    {
        $this->user = $user;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setAmount(?float $value): self
    {
        $this->amount = $value === null ? null :(string) $value;;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount === null ? $this->amount : (float) $this->amount;
    }

    public function setCurrency(?string $currency = null): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getArrivalDate(): ?\DateTime
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(?\DateTime $arrivalDate): self
    {
        $this->arrivalDate = $arrivalDate;
        return $this;
    }

    public function setStripeConnect(StripeConnect $stripeConnect = null): self
    {
        $this->stripeConnect = $stripeConnect;
        return $this;
    }

    public function onPrePersist(): void
    {
        $this->setCreatedAt(new \DateTime('now'));
    }
}
