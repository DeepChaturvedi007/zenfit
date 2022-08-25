<?php declare(strict_types=1);

namespace AppBundle\Consumer;

class VideoCompressEvent
{
    /** @var int[] */
    private array $messageIds;
    private bool $sendPush;
    private string $s3Key;
    private bool $isBulkMessage = false;

    /** @param int[] $messageIds */
    public function __construct(
        array $messageIds,
        bool $sendPush,
        string $s3Key,
        bool $isBulkMessage = false
    ) {
        $this->messageIds = $messageIds;
        $this->sendPush = $sendPush;
        $this->s3Key = $s3Key;
        $this->isBulkMessage = $isBulkMessage;
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
