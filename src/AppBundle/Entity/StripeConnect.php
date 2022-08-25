<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class StripeConnect
{
    use EntityIdTrait;

    const TYPE_FEE = 1;
    const TYPE_REFUND = 2;

    const TYPES = [
        self::TYPE_FEE => 'fees',
        self::TYPE_REFUND => 'refunds'
    ];

    private float $amount = 0;
    private User $user;
    private string $currency;
    private \DateTime $createdAt;
    private int $type = self::TYPE_FEE;
    private ?PaymentsLog $paymentsLog = null;

    public function __construct(User $user, string $currency)
    {
        $this->createdAt = new \DateTime();
        $this->currency = $currency;
        $this->user = $user;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getPaymentsLog(): ?PaymentsLog
    {
        return $this->paymentsLog;
    }
}
