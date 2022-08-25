<?php declare(strict_types=1);

namespace AppBundle\Entity;

class AwsMediaConvertDataItem
{
    private ?int $id = null;
    private string $messageIds;

    public function __construct(string $messageIds)
    {
        $this->messageIds = $messageIds;
    }

    public function getMessageIds(): string
    {
        return $this->messageIds;
    }

    /** @return array<mixed> */
    public function getMessageIdsArray(): array
    {
        return explode(',', $this->messageIds);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
