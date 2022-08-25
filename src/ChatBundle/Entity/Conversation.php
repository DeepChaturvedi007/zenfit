<?php declare(strict_types=1);

namespace ChatBundle\Entity;

use App\EntityIdTrait;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Conversation
{
    use EntityIdTrait;

    private User $user;
    private bool $deleted = false;
    /** @var Collection<Message> */
    private Collection $messages;
    private Client $client;

    public function __construct(User $user, Client $client)
    {
        $this->user = $user;
        $this->client = $client;
        $this->messages = new ArrayCollection();
    }

    public function getLatestMessage(): ?Message
    {
        $lastMessage = $this->messages->last();

        return $lastMessage === false ? null : $lastMessage;
    }

    public function getOldestUnreadMessage()
    {
        return $this->messages
            ->filter(function(Message $msg) {
                return $msg->getClient() && $msg->getIsNew();
            })->first();
    }

    public function getUnansweredMessagesCount()
    {
        $client = $this->getClient();
        return collect($client->getClientStatus())
            ->filter(function($clientStatus) {
                return !$clientStatus->getResolved() && $clientStatus->getEvent()->getName() === Event::SENT_MESSAGE;
            })->count();
    }

    public function unreadMessageFromClient()
    {
        $unreadMessages = $this->messages
            ->filter(function(Message $msg) {
                return $msg->getClient() && $msg->getIsNew();
            });

        if (!$unreadMessages->isEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Add message
     *
     * @param \ChatBundle\Entity\Message $message
     *
     * @return Conversation
     */
    public function addMessage(\ChatBundle\Entity\Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \ChatBundle\Entity\Message $message
     */
    public function removeMessage(\ChatBundle\Entity\Message $message)
    {
        $this->messages->removeElement($message);
    }

    public function getMessages(): Collection
    {
        return $this->messages;
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

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return Conversation
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
