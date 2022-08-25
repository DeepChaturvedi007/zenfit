<?php declare(strict_types=1);

namespace AppBundle\Consumer;

use AppBundle\Services\ErrorHandlerService;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class AwsEventBridgeSerializer implements SerializerInterface
{
    private ErrorHandlerService $errorHandlerService;

    public function __construct(ErrorHandlerService $errorHandlerService)
    {
        $this->errorHandlerService = $errorHandlerService;
    }

    /** @param array<mixed> $encodedEnvelope */
    public function decode(array $encodedEnvelope): Envelope
    {
        if (empty($encodedEnvelope['body'])) {
            throw new MessageDecodingFailedException('Encoded envelope should have at least a "body"');
        }

        $message = new MediaCompressedEvent(json_decode($encodedEnvelope['body'], true, 512, JSON_THROW_ON_ERROR));
        return new Envelope($message);
    }

    /** @return array<mixed> */
    public function encode(Envelope $envelope): array
    {
        $this->errorHandlerService->captureException(new \RuntimeException('Unsupported'));

        return [
            'body' => [
                'ignore_redeliver' => true,
            ]
        ];
    }
}
