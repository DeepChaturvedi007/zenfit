<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class UserSubscription
{
    use EntityIdTrait;

    private ?string $stripeCustomer = null;
    private ?string $stripeSubscription = null;
    private ?string $vat = null;
    private ?\DateTime $subscribedDate = null;
    private ?string $canceledAt = null;
    private bool $canceled = false;
    private bool $lastPaymentFailed = false;
    private ?string $nextPaymentAttempt = null;
    private int $attemptCount = 0;
    private ?string $invoiceUrl = null;
    private User $user;
    private ?Subscription $subscription = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function setStripeCustomer(?string $stripeCustomer): self
    {
        $this->stripeCustomer = $stripeCustomer;
        return $this;
    }

    public function getStripeCustomer(): ?string
    {
        return $this->stripeCustomer;
    }

    public function setStripeSubscription(?string $stripeSubscription): self
    {
        $this->stripeSubscription = $stripeSubscription;
        return $this;
    }

    public function getStripeSubscription(): ?string
    {
        return $this->stripeSubscription;
    }

    public function setSubscribedDate(?\DateTime $subscribedDate = null): self
    {
        $this->subscribedDate = $subscribedDate;
        return $this;
    }

    public function getSubscribedDate(): ?\DateTime
    {
        return $this->subscribedDate;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;
        return $this;
    }

    public function getCanceled(): bool
    {
        return $this->canceled;
    }

    public function setCanceledAt(?string $canceledAt = null): self
    {
        $this->canceledAt = $canceledAt;
        return $this;
    }

    public function getCanceledAt(): ?string
    {
        return $this->canceledAt;
    }

    public function setLastPaymentFailed(bool $lastPaymentFailed): self
    {
        $this->lastPaymentFailed = $lastPaymentFailed;
        return $this;
    }

    public function getLastPaymentFailed(): bool
    {
        return $this->lastPaymentFailed;
    }

    public function setNextPaymentAttempt(?string $nextPaymentAttempt): self
    {
        $this->nextPaymentAttempt = $nextPaymentAttempt;
        return $this;
    }

    public function getNextPaymentAttempt(): ?string
    {
        return $this->nextPaymentAttempt;
    }

    public function setAttemptCount(int $attemptCount): self
    {
        $this->attemptCount = $attemptCount;
        return $this;
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setInvoiceUrl(?string $invoiceUrl = null): self
    {
        $this->invoiceUrl = $invoiceUrl;
        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setVat(?string $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }
}
