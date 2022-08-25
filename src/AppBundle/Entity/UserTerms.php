<?php declare(strict_types=1);

namespace AppBundle\Entity;

class UserTerms
{
    private ?int $id = null;

    private string $terms;

    private User $user;

    public function __construct(User $user, string $terms)
    {
        $this->user = $user;
        $this->terms = $terms;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTerms(string $terms): self
    {
        $this->terms = $terms;

        return $this;
    }

    public function getTerms(): string
    {
        return $this->terms;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
