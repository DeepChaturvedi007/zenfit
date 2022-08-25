<?php

namespace AppBundle\Entity;

class Answer implements \Stringable
{
    private ?int $id = null;

    public function __construct(private string $answer, private Question $question, private Client $client)
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function __toString(): string
    {
        return $this->answer;
    }
}
