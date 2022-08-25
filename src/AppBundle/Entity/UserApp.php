<?php declare(strict_types=1);

namespace AppBundle\Entity;

class UserApp
{
    private ?int $id = null;
    private string $onesignal_app_id;
    private string $onesignal_app_key;
    private User $user;
    private string $iphone_link;
    private string $android_link;
    private string $title;

    public function __construct(User $user, string $onesignal_app_id, string $onesignal_app_key, string $iphone_link, string $android_link, string $title)
    {
        $this->user = $user;
        $this->onesignal_app_id = $onesignal_app_id;
        $this->onesignal_app_key = $onesignal_app_key;
        $this->iphone_link = $iphone_link;
        $this->android_link = $android_link;
        $this->title = $title;
    }

    /** @return array<string, mixed> */
    public function serialize(): array
    {
        return [
            'title' => $this->title,
            'iphone' => $this->iphone_link,
            'android' => $this->android_link
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setOnesignalAppId(string $onesignalAppId): self
    {
        $this->onesignal_app_id = $onesignalAppId;

        return $this;
    }

    public function getOnesignalAppId(): string
    {
        return $this->onesignal_app_id;
    }

    public function setOnesignalAppKey(string $onesignalAppKey): self
    {
        $this->onesignal_app_key = $onesignalAppKey;

        return $this;
    }

    public function getOnesignalAppKey(): string
    {
        return $this->onesignal_app_key;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setIphoneLink(string $iphoneLink): self
    {
        $this->iphone_link = $iphoneLink;

        return $this;
    }

    public function getIphoneLink(): string
    {
        return $this->iphone_link;
    }

    public function setAndroidLink(string $androidLink): self
    {
        $this->android_link = $androidLink;

        return $this;
    }

    public function getAndroidLink(): string
    {
        return $this->android_link;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
