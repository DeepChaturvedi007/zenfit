<?php declare(strict_types=1);

namespace AppBundle\Entity;

class Queue
{
    private ?int $id = null;
    private string $name;
    private string $email;
    private int $status;
    private ?string $datakey = null;
    private int $type;
    private int $notifiedTrainer = 0;
    private ?Client $client = null;
    private \DateTime $createdAt;
    private ?string $subject = null;
    private bool $survey = false;

    public const STATUS_PENDING = 0;
    public const STATUS_SENT = 1;
    public const STATUS_CONFIRMED = 2;
    public const STATUS_ERROR = 3;
    public const STATUS_SENDGRID_DEFERRED = 4;
    public const STATUS_SENDGRID_DELIVERED = 5;
    public const STATUS_SENDGRID_UNKNOWN = 6;
    public const STATUS_SENDGRID_DROPPED = 7;
    public const STATUS_SENDGRID_BOUNCED = 8;
    public const STATUS_SENDGRID_OPENED = 9;
    public const STATUS_SENDGRID_CLICKED = 10;
    public const STATUS_LEAD_SURVEY = 12;
    public const STATUS_TEMPORARY = 13;
    public const STATUS_CANCELED = 14;

    public const TYPE_CLIENT_EMAIL = 2;
    public const TYPE_MESSAGE_TO_TRAINER = 14;
    public const TYPE_CLIENT_MESSAGE_NOTIFICATION = 13;

    public function __construct(string $email, string $name, int $status, int $type)
    {
        $this->email = $email;
        $this->name = $name;
        $this->status = $status;
        $this->type = $type;
        $this->createdAt = new \DateTime();
    }

    public function getQuestionnaireSurveyOnlyUrl(string $appHostname): string
    {
        return "{$appHostname}/client/clientActivation?datakey={$this->getDataKey()}&only_survey=1";
    }

    public function getClientCreationLink(string $appHostname): string
    {
        return "{$appHostname}/client/clientActivation?datakey={$this->getDatakey()}";
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setDatakey(?string $datakey): self
    {
        $this->datakey = $datakey;

        return $this;
    }

    public function getDatakey(): ?string
    {
        return $this->datakey;
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

    public function setNotifiedTrainer(int $notifiedTrainer): self
    {
        $this->notifiedTrainer = $notifiedTrainer;

        return $this;
    }

    public function getNotifiedTrainer(): int
    {
        return $this->notifiedTrainer;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    private ?User $user = null;

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
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

    private ?string $clientName = null;
    private ?string $clientEmail = null;

    public function setClientName(?string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientEmail(?string $clientEmail): self
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setSurvey(bool $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    public function getSurvey(): bool
    {
        return $this->survey;
    }

    private ?Payment $payment = null;

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    private ?string $message = null;

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}