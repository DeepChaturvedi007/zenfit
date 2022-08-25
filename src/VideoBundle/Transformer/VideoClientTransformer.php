<?php

namespace VideoBundle\Transformer;

use AppBundle\Entity\VideoClient;
use AppBundle\Services\TrainerAssetsService;
use League\Fractal\TransformerAbstract;

class VideoClientTransformer extends TransformerAbstract
{
    private TrainerAssetsService $trainerAssetsService;

    public function __construct(TrainerAssetsService $trainerAssetsService)
    {
        $this->trainerAssetsService = $trainerAssetsService;
    }

    /** @return array<string, mixed> */
    public function transform(VideoClient $videoClient): array
    {
        $trainerAssets = $this->trainerAssetsService;

        $video = $videoClient->getVideo();

        $url = $video->getUrl();
        $youtubeId = null;
        try {
            if ($url !== null) {
                $youtubeId = $trainerAssets->getYoutubeId($url);
            }
        } catch (\Exception) {
            $youtubeId = null;
        }

        $createdAt = $video->getCreatedAt();

        return [
            'id' => $video->getId(),
            'title' => $video->getTitle(),
            'url' => $url,
            'youtubeId' => $youtubeId,
            'comment' => $video->getComment(),
            'picture' => $video->getPicture(),
            'tags' => $video->tagsList(),
            'timeAgo' => $createdAt ? $this->timeAgo($createdAt->getTimestamp()) : null,
            'createdAt' => $createdAt ? $createdAt->format('Y-m-d H:i:s') : null,
            'isNew' => $videoClient->isNew()
        ];
    }

    private function timeAgo(int $time): string
    {
        $estimateTime = time() - $time;

        if($estimateTime < 1) {
            return 'less than a second ago';
        }

        $condition = [
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        ];

        foreach($condition as $secs => $str)
        {
            $d = $estimateTime / $secs;
            if($d >= 1)
            {
                $r = round($d);
                return 'about ' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }

        return '';
    }
}
