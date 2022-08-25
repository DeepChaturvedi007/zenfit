<?php

namespace ChatBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ChatMessageEvent extends Event
{
    public const NAME = 'chat.message_deliver';

    /** @var array<int> */
    private array $ids;
    private ?string $video;
    private bool $hasPushNotification;
    private bool $isBulkMessage = false;
    private ?\Exception $error = null;

    /** @param array<int> $ids */
    public function __construct(array $ids, bool $hasPushNotification = false, ?string $video = null, bool $isBulkMessage = false)
    {
        $this->ids = $ids;
        $this->hasPushNotification = $hasPushNotification;
        $this->video = $video;
        $this->isBulkMessage = $isBulkMessage;
    }

    /** @return array<int> */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function hasPushNotification(): bool
    {
        return $this->hasPushNotification;
    }

    public function isBulkMessage(): bool
    {
        return $this->isBulkMessage;
    }

    public function getError(): ?\Exception
    {
        return $this->error;
    }

    public function setError(\Exception $error): self
    {
        $this->error = $error;

        return $this;
    }
}
