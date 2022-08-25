<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class Payment
{
    use EntityIdTrait;

    private string $datakey;
    private int $months = 0;
    private string $recurringFee = '0';
    private ?string $upfrontFee = '0';
    private ?float $applicationFee = null;
    private string $currency = 'usd';
    private ?string $terms = null;
    private ?string $trialEnd = null;
    private ?\DateTime $sentAt = null;
    private bool $charged = false;
    private bool $delayUpfront = false;
    private bool $deleted = false;
    private Client $client;
    private ?Plan $plan = null;
    private ?Lead $lead = null;
    private ?ClientStripe $clientStripe = null;
    private ?Queue $queue = null;
    private ?User $salesPerson = null;

    public function __construct(Client $client, string $datakey)
    {
        $this->datakey = $datakey;
        $this->client = $client;
    }

    public function getCharged(): bool
    {
        return $this->charged;
    }

    public function setCharged(bool $charged): self
    {
        $this->charged = $charged;
        return $this;
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

    public function setMonths(int $months): self
    {
        $this->months = $months;
        return $this;
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function setRecurringFee(float $recurringFee): self
    {
        $this->recurringFee = (string) $recurringFee;
        return $this;
    }

    public function getRecurringFee(): float
    {
        return (float) $this->recurringFee;
    }

    public function setUpfrontFee(?float $value): self
    {
        $this->upfrontFee = $value === null ? null :(string) $value;
        return $this;
    }

    public function getUpfrontFee(): ?float
    {
        return $this->upfrontFee === null ? $this->upfrontFee : (float) $this->upfrontFee;
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

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setSalesPerson(?User $salesPerson = null): self
    {
        $this->salesPerson = $salesPerson;
        return $this;
    }

    public function getSalesPerson(): ?User
    {
        return $this->salesPerson;
    }

    public function setLead(?Lead $lead = null): self
    {
        $this->lead = $lead;
        return $this;
    }

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setSentAt(?\DateTime $sentAt = null): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setQueue(?Queue $queue = null): self
    {
        $this->queue = $queue;
        return $this;
    }

    public function getQueue(): ?Queue
    {
        return $this->queue;
    }

    public function setClientStripe(?ClientStripe $clientStripe = null): self
    {
        $this->clientStripe = $clientStripe;
        return $this;
    }

    public function getClientStripe(): ?ClientStripe
    {
        return $this->clientStripe;
    }

    public function setTrialEnd(?string $trialEnd = null): self
    {
        $this->trialEnd = $trialEnd;
        return $this;
    }

    public function getTrialEnd(): ?string
    {
        return $this->trialEnd;
    }

    public function setTerms(?string $terms = null): self
    {
        $this->terms = $terms;
        return $this;
    }

    public function getTerms(): ?string
    {
        return $this->terms;
    }

    public function setPlan(?Plan $plan = null): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function getDelayUpfront(): bool
    {
        return $this->delayUpfront;
    }

    public function setDelayUpfront(bool $delayUpfront): self
    {
        $this->delayUpfront = $delayUpfront;
        return $this;
    }

    public function setApplicationFee(?float $applicationFee = null): self
    {
        $this->applicationFee = $applicationFee;
        return $this;
    }

    public function getApplicationFee(): ?float
    {
        return $this->applicationFee;
    }
}
