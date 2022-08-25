<?php declare(strict_types=1);

namespace AppBundle\Consumer;

use AppBundle\Repository\AwsMediaConvertDataItemRepository;
use AppBundle\Services\ErrorHandlerService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use ChatBundle\Event\ChatMessageEvent;

class MediaCompressedHandler implements MessageHandlerInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private AwsMediaConvertDataItemRepository $awsMediaConvertDataItemRepository;
    private string $s3rootUrl;
    private string $s3ImagesBucket;
    private ErrorHandlerService $errorHandlerService;

    public function __construct(
        string $s3rootUrl,
        string $s3ImagesBucket,
        ErrorHandlerService $errorHandlerService,
        AwsMediaConvertDataItemRepository $awsMediaConvertDataItemRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->awsMediaConvertDataItemRepository = $awsMediaConvertDataItemRepository;
        $this->s3rootUrl = $s3rootUrl;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->errorHandlerService = $errorHandlerService;
    }

    public function __invoke(MediaCompressedEvent $event): void
    {
        $messageBody = $event->getMessageBody();
        if (isset($messageBody['ignore_redeliver']) && $messageBody['ignore_redeliver'] === true) {
            return;
        }

        if (!array_key_exists('detail', $messageBody)
            || !array_key_exists('userMetadata', $messageBody['detail'])
            || !array_key_exists('jobId', $messageBody['detail']['userMetadata'])
            || !array_key_exists('type', $messageBody['detail']['userMetadata'])
            || !array_key_exists('pushNotification', $messageBody['detail']['userMetadata'])
            || !array_key_exists('isBulkMessage', $messageBody['detail']['userMetadata'])
            || !array_key_exists('outputGroupDetails', $messageBody['detail'])
        ) {
            throw new \RuntimeException('Malformed message body');
        }

        $jobId = $messageBody['detail']['userMetadata']['jobId'];

        $jobData = $this->awsMediaConvertDataItemRepository->find($jobId);
        if ($jobData === null) {
            throw new \RuntimeException('Job data not found');
        }

        $messageIds = $jobData->getMessageIdsArray();

        $pushNotification = (bool) $messageBody['detail']['userMetadata']['pushNotification'];
        $isBulkMessage = (bool) $messageBody['detail']['userMetadata']['isBulkMessage'];
        $url = $this->s3rootUrl . str_replace('s3://'.$this->s3ImagesBucket.'/', '', $messageBody['detail']['outputGroupDetails'][0]['outputDetails'][0]['outputFilePaths'][0]);

        try {
            $this->eventDispatcher->dispatch(
                new ChatMessageEvent($messageIds, $pushNotification, $url, $isBulkMessage),
                ChatMessageEvent::NAME
            );
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
        }
    }
}
