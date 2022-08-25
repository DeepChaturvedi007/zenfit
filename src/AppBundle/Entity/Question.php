<?php

namespace AppBundle\Entity;

use App\EntityIdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Question
{
    use EntityIdTrait;

    public const TYPE_SURVEY = 1;

    public const INPUT_TYPE_INPUT = 1;
    public const INPUT_TYPE_SLIDER = 2;
    public const INPUT_TYPE_SINGLE_CHOICE = 3;
    public const INPUT_TYPE_MULTIPLE_CHOICE = 4;
    public const INPUT_TYPE_MULTIPLE_INPUT = 5;

    public const INPUT_TYPES = [
        self::INPUT_TYPE_INPUT,
        self::INPUT_TYPE_SLIDER,
        self::INPUT_TYPE_SINGLE_CHOICE,
        self::INPUT_TYPE_MULTIPLE_CHOICE,
        self::INPUT_TYPE_MULTIPLE_INPUT
    ];

    public const TYPES = [
        self::TYPE_SURVEY,
    ];

    private string $text;
    /** @var array<string> */
    private ?array $options = null;
    private int $inputType;
    private int $type;
    private string $placeholder = '';
    private ?string $subtitle = null;
    private int $order = 0;
    private ?int $defaultValue = null;
    private bool $deleted = false;
    private User $user;
    /** @var Collection<int, Answer> */
    private Collection $answers;

    public function __construct(User $user, string $text, int $type, int $inputType)
    {
        $this->answers = new ArrayCollection();
        $this->user = $user;
        $this->text = $text;

        if (!in_array($type, self::TYPES,true)) {
            throw new \RuntimeException('Unsupported input type');
        }

        $this->type = $type;

        if (!in_array($inputType, self::INPUT_TYPES,true)) {
            throw new \RuntimeException('Unsupported input type');
        }

        $this->inputType = $inputType;
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

    public function getInputType(): int
    {
        return $this->inputType;
    }

    /** @return array<string> */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /** @param array<string> $value */
    public function setOptions(array $value): void
    {
        $this->options = $value;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function getAnswer(Client $client): ?Answer
    {
        foreach ($client->getAnswers() as $answer) {
            if ($answer->getQuestion() === $this) {
                return $answer;
            }
        }

        return null;
    }

    public function doAnswer(Client $client, string $answerValue): void
    {
        $answer = $this->getAnswer($client);
        if ($answer === null) {
            $client->addAnswer(new Answer($answerValue, $this, $client));
        } else {
            $answer->setAnswer($answerValue);
        }
    }
}
