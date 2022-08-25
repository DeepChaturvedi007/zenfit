<?php declare(strict_types=1);

namespace AppBundle\Entity;

class UserStripe
{
    private ?int $id = null;
    private bool $paymentRequired = false;
    private float $feePercentage = 2.4;
    private bool $applicationFeeRequired = true;
    private bool $klarnaEnabled = false;
    private bool $sepaEnabled = false;
    private ?string $klarnaCountry = null;

    public function __construct(private User $user, private string $stripeUserId, private string $stripeRefreshToken)
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripeUserId(): string
    {
        return $this->stripeUserId;
    }

    public function getStripeRefreshToken(): string
    {
        return $this->stripeRefreshToken;
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

    public function setPaymentRequired(bool $paymentRequired): self
    {
        $this->paymentRequired = $paymentRequired;
        return $this;
    }

    public function getPaymentRequired(): bool
    {
        return $this->paymentRequired;
    }

    public function setApplicationFeeRequired(bool $applicationFeeRequired): self
    {
        $this->applicationFeeRequired = $applicationFeeRequired;
        return $this;
    }

    public function getApplicationFeeRequired(): bool
    {
        return $this->applicationFeeRequired;
    }

    public function setFeePercentage(float $feePercentage): self
    {
        $this->feePercentage = $feePercentage;
        return $this;
    }

    public function getFeePercentage(): float
    {
        return $this->feePercentage;
    }

    public function setKlarnaEnabled(bool $klarnaEnabled): self
    {
        $this->klarnaEnabled = $klarnaEnabled;
        return $this;
    }

    public function getKlarnaEnabled(): bool
    {
        return $this->klarnaEnabled;
    }

    public function setSepaEnabled(bool $sepaEnabled): self
    {
        $this->sepaEnabled = $sepaEnabled;
        return $this;
    }

    public function getSepaEnabled(): bool
    {
        return $this->sepaEnabled;
    }

    public function setKlarnaCountry(string $klarnaCountry): self
    {
        $this->klarnaCountry = $klarnaCountry;
        return $this;
    }

    public function getKlarnaCountry(): ?string
    {
        return $this->klarnaCountry;
    }
}
