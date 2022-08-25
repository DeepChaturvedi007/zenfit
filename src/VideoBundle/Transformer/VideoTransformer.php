<?php

namespace VideoBundle\Transformer;

use League\Fractal\TransformerAbstract;
use AppBundle\Entity\Video;

class VideoTransformer extends TransformerAbstract
{
    /** @return array<string, mixed> */
    public function transform(Video $video): array
    {
        return [
            'id' => $video->getId(),
            'title' => $video->getTitle(),
            'url' => $video->getUrl()
        ];
    }
}
