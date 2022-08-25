<?php declare(strict_types=1);

namespace AppBundle\Entity;

class VideoTag
{
    private ?int $id = null;

    private ?string $title; //TODO maybe this should not be nullable

    private Video $video;


    public function __construct(Video $video, ?string $title)
    {
        $this->video = $video;
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }
}
