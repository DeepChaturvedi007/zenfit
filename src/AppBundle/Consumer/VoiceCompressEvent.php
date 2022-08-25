<?php declare(strict_types=1);

namespace AppBundle\Consumer;

class VoiceCompressEvent
{
    public function __construct(
        /** @var int[] */
        private array $messageIds,
        private bool $sendPush,
        private string $s3Key,
        private bool $isBulkMessage = false
    )
    {
    }

    /** @return int[] */
    public function getMessageIds(): array
    {
        return $this->messageIds;
    }

    public function getSendPush(): bool
    {
        return $this->sendPush;
    }

    public function getS3Key(): string
    {
        return $this->s3Key;
    }

    public function isBulkMessage(): bool
    {
        return $this->isBulkMessage;
    }
}
