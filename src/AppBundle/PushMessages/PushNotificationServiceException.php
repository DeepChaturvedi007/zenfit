<?php declare(strict_types=1);

namespace AppBundle\PushMessages;

class PushNotificationServiceException extends \Exception
{
    /** @var array<mixed> */
    private array $response = [];

    /** @return array<mixed> */
    public function getResponse(): array
    {
        return $this->response;
    }

    /** @param array<mixed> $response */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }
}
