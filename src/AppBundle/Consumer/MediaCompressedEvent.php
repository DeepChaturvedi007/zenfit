<?php declare(strict_types=1);

namespace AppBundle\Consumer;

class MediaCompressedEvent
{
    /** @var array<mixed> $messageBody */
    private array $messageBody;

    /** @param array<mixed> $messageBody */
    public function __construct(array $messageBody)
    {
        $this->messageBody = $messageBody;
    }

    /** @return array<mixed> */
    public function getMessageBody(): array
    {
        return $this->messageBody;
    }
}
