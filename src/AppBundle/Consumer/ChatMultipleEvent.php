<?php declare(strict_types=1);

namespace AppBundle\Consumer;

class ChatMultipleEvent
{
    public function __construct(
        /** @var int[] */
        private array $clientIds,
        private ?string $message,
        private ?string $media,
        private bool $sendPush
    )
    {
    }

    /** @return int[] */
    public function getClientIds(): array
    {
        return $this->clientIds;
    }

    public function getSendPush(): bool
    {
        return $this->sendPush;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }
}
