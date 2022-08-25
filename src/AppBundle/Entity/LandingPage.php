<?php declare(strict_types=1);

namespace AppBundle\Entity;

class LandingPage
{
	private ?int $id = null;
	private ?string $headline = null;
	private ?string $subtitle = null;
	private ?string $success_headline;
	private ?string $success_subtitle;
	private string $background_image;
	private User $user;

	public function __construct(User $user, string $slug, string $backgroundImage, string $success_headline, string $success_subtitle)
    {
        $this->success_headline = $success_headline;
        $this->success_subtitle = $success_subtitle;
        $this->user = $user;
        $this->slug = $slug;
        $this->background_image = $backgroundImage;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set headline
     *
     * @param string $headline
     *
     * @return LandingPage
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;

        return $this;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSuccessHeadline(?string $successHeadline): self
    {
        $this->success_headline = $successHeadline;

        return $this;
    }

    public function getSuccessHeadline(): ?string
    {
        return $this->success_headline;
    }

    public function setSuccessSubtitle(?string $successSubtitle): self
    {
        $this->success_subtitle = $successSubtitle;

        return $this;
    }

    public function getSuccessSubtitle(): ?string
    {
        return $this->success_subtitle;
    }

	public function setBackgroundImage(string $backgroundImage): self
	{
		$this->background_image = $backgroundImage;

		return $this;
	}

	public function getBackgroundImage(): string
    {
		return $this->background_image;
	}

    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    private string $slug;

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    private ?string $website = null;

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    private ?string $success_button = null;

    public function setSuccessButton(?string $successButton): self
    {
        $this->success_button = $successButton;

        return $this;
    }

    public function getSuccessButton(): ?string
    {
        return $this->success_button;
    }
}
