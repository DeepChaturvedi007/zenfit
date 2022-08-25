<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientSettings
 *
 * @ORM\Table(name="client_settings")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ClientSettingsRepository")
 */
class ClientSettings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="client_id", type="integer", unique=true) */
    private Client $client;

    /** @ORM\Column(name="mfp_url", type="string", length=255) */
    private ?string $mfpUrl = null;

    /** @ORM\Column(name="mfp_user_id", type="string", length=255, nullable=true) */
    private ?string $mfpUserId = null;

    /** @ORM\Column(name="mfp_access_token", type="string", length=512, nullable=true) */
    private ?string $mfpAccessToken = null;

    /** @ORM\Column(name="mfp_refresh_token", type="string", length=512, nullable=true) */
    private ?string $mfpRefreshToken = null;

    /** @ORM\Column(name="mfp_expire_date", type="datetime", nullable=true) */
    private ?\DateTime $mfpExpireDate = null;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get clientId
     *
     *  @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set mfpUrl.
     *
     * @param string|null $url
     * @return ClientSettings
     */
    public function setMfpUrl($url = null)
    {
        $this->mfpUrl = $url;

        return $this;
    }

    /**
     * Get mfpUrl.
     *
     * @return string|null
     */
    public function getMfpUrl()
    {
        return $this->mfpUrl;
    }

    public function getMfpUserId()
    {
        return $this->mfpUserId;
    }

    public function setMfpUserId($id)
    {
        $this->mfpUserId = $id;

        return $this;
    }

    public function getMfpAccessToken()
    {
        return $this->mfpAccessToken;
    }

    public function setMfpAccessToken($token)
    {
        $this->mfpAccessToken = $token;

        return $this;
    }

    public function getMfpRefreshToken()
    {
        return $this->mfpRefreshToken;
    }

    public function setMfpRefreshToken($token)
    {
        $this->mfpRefreshToken = $token;

        return $this;
    }

    public function getMfpExpireDate()
    {
        return $this->mfpExpireDate;
    }

    public function setMfpExpireDate(\DateTime $date)
    {
        $this->mfpExpireDate = $date;

        return $this;
    }
}
