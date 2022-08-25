<?php declare(strict_types=1);

namespace GymBundle\Entity;

use App\EntityIdTrait;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class Gym
{
    use EntityIdTrait;

    private ?string $name = null;
    private User $admin;
    private ?User $assignDataFrom = null;
    /** @var Collection<User> */
    private Collection $users;
    private bool $autoAssignLeads = false;

    public function __construct(User $admin)
    {
        $this->admin = $admin;
        $this->users = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAdmin(): User
    {
        return $this->admin;
    }

    public function setAdmin(User $admin): self
    {
        $this->admin = $admin;
        return $this;
    }

    public function getAssignDataFrom(): ?User
    {
        return $this->assignDataFrom;
    }

    /** @return User[] */
    public function getUsers(): array
    {
        $newCriteria = Criteria::create()
            ->where(Criteria::expr()->eq('deleted', 0));

        return $this->users->matching($newCriteria)->toArray();
    }

    public function getAutoAssignLeads(): bool
    {
        return $this->autoAssignLeads;
    }

    public function addUser(User $user): self
    {
        $this->users[] = $user;
        return $this;
    }

    public function onPrePersist(): void
    {

    }
}
