<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use \DateTime;

class Lead
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $phone = null;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;
    private ?DateTime $followUpAt = null;
    private bool $viewed = false;
    private int $status = 1;
    private bool $followUp = false;
    private bool $inDialog = false;
    private ?string $dialogMessage = null;
    private ?string $salesNotes = null;
    private bool $deleted = false;
    private ?string $utm = null;
    private int $contactTime = 0;
    /** @var Collection<LeadTag> */
    private Collection $tags;
    private User $user;
    private ?Payment $payment = null;
    private ?Client $client = null;

    public const LEAD_NEW = 1;
    public const LEAD_IN_DIALOG = 2;
    public const LEAD_WON = 3;
    public const LEAD_LOST = 4;
    public const LEAD_PAYMENT_WAITING = 5;
    public const LEAD_NO_ANSWER = 8;

    public const LEAD_STATUS = [
        self::LEAD_NEW => 'New',
        self::LEAD_IN_DIALOG => 'In Dialog',
        self::LEAD_WON => 'Won',
        self::LEAD_LOST => 'Lost',
        self::LEAD_PAYMENT_WAITING => 'Payment Waiting',
        self::LEAD_NO_ANSWER => 'No Answer'
    ];

    public const CONTACT_TIME = [
        0 => 'Whenever',
        1 => '9-12',
        2 => '12-15',
        3 => '15-18',
        4 => '18-21'
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->tags = new ArrayCollection();
    }

    public function addTag(LeadTag $tag): self
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function removeTag(LeadTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

		/** @return array<string|null> */
    public function tagsList(): array
    {
        if(!$this->getTags()) {
          	return [];
        }

        return array_map(function(LeadTag $tag) {
            return $tag->getTitle();
        }, $this->getTags()->toArray());
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setName(?string $name = null): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEmail(?string $email = null): self
    {
        $this->email = $email;
				return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setCreatedAt(?DateTime $createdAt = null): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setViewed(bool $viewed): self
    {
        $this->viewed = $viewed;
        return $this;
    }

    public function getViewed(): bool
    {
        return $this->viewed;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setFollowUpAt(?DateTime $followUpAt): self
    {
        $this->followUpAt = $followUpAt;
        return $this;
    }

    public function getFollowUpAt(): ?DateTime
    {
        return $this->followUpAt;
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

    public function setFollowUp(bool $followUp = false): self
    {
        $this->followUp = $followUp;
        return $this;
    }

    public function getFollowUp(): bool
    {
        return $this->followUp;
    }

    public function setInDialog(bool $inDialog): self
    {
        $this->inDialog = $inDialog;
        return $this;
    }

    public function getInDialog(): bool
    {
        return $this->inDialog;
    }

    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;
        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setClient(Client $client = null): self
    {
        $this->client = $client;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setDialogMessage(?string $dialogMessage = null): self
    {
        $this->dialogMessage = $dialogMessage;
        return $this;
    }

    public function getDialogMessage(): ?string
    {
        return $this->dialogMessage;
    }

    public function setSalesNotes(?string $salesNotes = null): self
    {
        $this->salesNotes = $salesNotes;
        return $this;
    }

    public function getSalesNotes(): ?string
    {
        return $this->salesNotes;
    }

    public function setUtm(?string $utm = null): self
    {
        $this->utm = $utm;
        return $this;
    }

    public function getUtm(): ?string
    {
        return $this->utm;
    }

    public function setContactTime(int $contactTime = 0): self
    {
            $this->contactTime = $contactTime;
            return $this;
    }

    public function getContactTime(): int
    {
        return $this->contactTime;
    }
}
