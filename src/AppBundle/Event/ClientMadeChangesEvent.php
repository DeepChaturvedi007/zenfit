<?php

namespace AppBundle\Event;

use AppBundle\Entity\Client;
use ChatBundle\Entity\Message;
use Symfony\Contracts\EventDispatcher\Event;


class ClientMadeChangesEvent extends Event
{
    public function __construct(
        protected Client $client,
        /**
         * @var string
         */
        protected $name,
        protected ?Message $message = null
    )
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
