<?php declare(strict_types=1);

namespace AppBundle\Consumer;

use AppBundle\Entity\AwsMediaConvertDataItem;
use AppBundle\Repository\AwsMediaConvertDataItemRepository;
use AppBundle\Services\AwsService;
use AppBundle\Services\ErrorHandlerService;
use Aws\MediaConvert\MediaConvertClient;
use Psr\Log\LoggerInterface;

class VideoCompressHandler implements MessageHandlerInterface
{
    public const TYPE = 'video';

    private LoggerInterface $logger;
    private string $s3ImagesKeyPrefix;
    private string $s3ImagesBucket;
    private ErrorHandlerService $errorHandlerService;
    private MediaConvertClient $mediaConvertClient;
    private string $awsMediaConvertRole;
    private string $env;
    private AwsService $awsService;
    private AwsMediaConvertDataItemRepository $awsMediaConvertDataItemRepository;

    public function __construct(
        ErrorHandlerService $errorHandlerService,
        MediaConvertClient $mediaConvertClient,
        AwsService $awsService,
        AwsMediaConvertDataItemRepository $awsMediaConvertDataItemRepository,
        string $awsMediaConvertRole,
        string $env,
        string $s3ImagesKeyPrefix,
        string $s3ImagesBucket,
        LoggerInterface $logger
    ) {
        $this->mediaConvertClient = $mediaConvertClient;
        $this->errorHandlerService = $errorHandlerService;
        $this->awsMediaConvertDataItemRepository = $awsMediaConvertDataItemRepository;
        $this->logger = $logger;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->env = $env;
        $this->awsMediaConvertRole = $awsMediaConvertRole;
        $this->awsService = $awsService;
    }

    public function __invoke(VideoCompressEvent $event): void
    {
        try {
            $sourceKey = $event->getS3Key();

            if (strpos($sourceKey, '.quicktime') !== false) {
                $this->awsService->getClient()->copy($this->s3ImagesBucket, $sourceKey, $this->s3ImagesBucket, str_replace('.quicktime', '.mov', $sourceKey));

                $oldSourceKey = $sourceKey;
                $sourceKey = str_replace('.quicktime', '.mov', $sourceKey);

                $this->awsService->getClient()->deleteObject([
                    'Bucket' => $this->s3ImagesBucket,
                    'Key' => $oldSourceKey,
                ]);
            }

            $jobSetting = [
            "OutputGroups" => [
                [
                    "Name" => "File Group",
                    "OutputGroupSettings" => [
                        "Type" => "FILE_GROUP_SETTINGS",
                        "FileGroupSettings" => [
                            "Destination" => "s3://$this->s3ImagesBucket/$this->s3ImagesKeyPrefix"
                        ]
                    ],
                    "Outputs" => [
                        [
                            "VideoDescription" => [
                                "ScalingBehavior" => "DEFAULT",
                                "TimecodeInsertion" => "DISABLED",
                                "CodecSettings" => [
                                    "Codec" => "H_264",
                                    "H264Settings" => [
                                        "InterlaceMode" => "PROGRESSIVE",
                                        "NumberReferenceFrames" => 3,
                                        "Syntax" => "DEFAULT",
                                        "Softness" => 0,
                                        "GopClosedCadence" => 1,
                                        "GopSize" => 90,
                                        "Slices" => 1,
                                        "GopBReference" => "DISABLED",
                                        "SlowPal" => "DISABLED",
                                        "SpatialAdaptiveQuantization" => "ENABLED",
                                        "TemporalAdaptiveQuantization" => "ENABLED",
                                        "FlickerAdaptiveQuantization" => "DISABLED",
                                        "EntropyEncoding" => "CABAC",
                                        "MaxBitrate" => 5000000,
                                        "RateControlMode" => "QVBR",
                                        "CodecProfile" => "MAIN",
                                        "Telecine" => "NONE",
                                        "MinIInterval" => 0,
                                        "AdaptiveQuantization" => "HIGH",
                                        "CodecLevel" => "AUTO",
                                        "FieldEncoding" => "PAFF",
                                        "SceneChangeDetect" => "ENABLED",
                                        "QualityTuningLevel" => "SINGLE_PASS",
                                        "FramerateConversionAlgorithm" => "DUPLICATE_DROP",
                                        "UnregisteredSeiTimecode" => "DISABLED",
                                        "GopSizeUnits" => "FRAMES",
                                        "ParControl" => "SPECIFIED",
                                        "NumberBFramesBetweenReferenceFrames" => 2,
                                        "RepeatPps" => "DISABLED",
                                        "FramerateNumerator" => 30,
                                        "FramerateDenominator" => 1,
                                        "ParNumerator" => 1,
                                        "ParDenominator" => 1
                                    ]
                                ],
                                "AfdSignaling" => "NONE",
                                "DropFrameTimecode" => "ENABLED",
                                "RespondToAfd" => "NONE",
                                "ColorMetadata" => "INSERT"
                            ],
                            "AudioDescriptions" => [
                                [
                                    "AudioTypeControl" => "FOLLOW_INPUT",
                                    "CodecSettings" => [
                                        "Codec" => "AAC",
                                        "AacSettings" => [
                                            "AudioDescriptionBroadcasterMix" => "NORMAL",
                                            "RateControlMode" => "CBR",
                                            "CodecProfile" => "LC",
                                            "CodingMode" => "CODING_MODE_2_0",
                                            "RawFormat" => "NONE",
                                            "SampleRate" => 48000,
                                            "Specification" => "MPEG4",
                                            "Bitrate" => 64000
                                        ]
                                    ],
                                    "LanguageCodeControl" => "FOLLOW_INPUT",
                                    "AudioSourceName" => "Audio Selector 1"
                                ]
                            ],
                            "ContainerSettings" => [
                                "Container" => "MP4",
                                "Mp4Settings" => [
                                    "CslgAtom" => "INCLUDE",
                                    "FreeSpaceBox" => "EXCLUDE",
                                    "MoovPlacement" => "PROGRESSIVE_DOWNLOAD"
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            "AdAvailOffset" => 0,
            "Inputs" => [
                [
                    "AudioSelectors" => [
                        "Audio Selector 1" => [
                            "Offset" => 0,
                            "DefaultSelection" => "NOT_DEFAULT",
                            "ProgramSelection" => 1,
                            "SelectorType" => "TRACK",
                            "Tracks" => [
                                1
                            ]
                        ]
                    ],
                    "VideoSelector" => [
                        "ColorSpace" => "FOLLOW",
                        "Rotate" => "AUTO"
                    ],
                    "FilterEnable" => "AUTO",
                    "PsiControl" => "USE_PSI",
                    "FilterStrength" => 0,
                    "DeblockFilter" => "DISABLED",
                    "DenoiseFilter" => "DISABLED",
                    "TimecodeSource" => "EMBEDDED",
                    "FileInput" => "s3://$this->s3ImagesBucket/$sourceKey"
                ]
            ],
            "TimecodeConfig" => [
                "Source" => "EMBEDDED"
            ]
        ];

            $this->logger->info('Start');

            $jobData = new AwsMediaConvertDataItem(implode(',', $event->getMessageIds()));
            $this->awsMediaConvertDataItemRepository->save($jobData);

            $this->mediaConvertClient->createJob([
                "Settings" => $jobSetting,
                "Role" => $this->awsMediaConvertRole,
                "UserMetadata" => [
                    'jobId' => $jobData->getId(),
                    'pushNotification' => $event->getSendPush() ? '1': '0',
                    'isBulkMessage' => $event->isBulkMessage() ? '1': '0',
                    'env' => $this->env,
                    'type' => self::TYPE,
                ],
            ]);

            $this->logger->info('Done');
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
            $this->logError($event, $e->getMessage());
            echo sprintf('Exit due to error: '.$e->getMessage()).PHP_EOL;
        }
    }

    private function logError(VideoCompressEvent $event, string $error): void
    {
        $data = [
            'error' => $error,
            'class' => __CLASS__,
            'message' => $event,
        ];

        $this->logger->error(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
