<?php declare(strict_types=1);

namespace TrainerBundle\Entity;

use AppBundle\Entity\User;

class Referral
{
    private ?int $id = null;
    private int $payout = 0;
    private ?string $name = null;
    private User $user;
    private int $status = 0;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function setStatus(int|string $status): self
    {
        $this->status = (int) $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setPayout(int|string $payout): self
    {
        $this->payout = (int) $payout;

        return $this;
    }

    public function getPayout(): int
    {
        return $this->payout;
    }
}
