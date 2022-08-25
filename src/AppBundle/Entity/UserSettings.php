<?php declare(strict_types=1);

namespace AppBundle\Entity;

use App\EntityIdTrait;

class UserSettings
{
    use EntityIdTrait;

    private ?string $profilePicture = null;
    private ?string $video = null;
    private ?string $videoThumb = null;
    private ?string $welcomeMessage = null;
    private ?string $companyName = null;
    private ?string $companyLogo = null;
    private ?string $address = null;
    private ?string $checkInQuestions = null;
    private ?string $backgroundImage = null;
    private ?string $mailChimpApiKey = null;
    private ?string $mailChimpListId = null;
    private ?string $primaryColor = null;
    private ?string $defaultCurrency = null;
    private ?string $questionnaireText = null;
    private ?int $defaultMonths = null;
    private ?int $defaultRecurring = null;
    private ?int $defaultUpfront = null;
    private ?int $defaultCheckInDay = null;
    private int $oldChatsInterval = 4;
    private int $checkInDuration = 7;
    private User $user;
    private bool $autoDeactivate = true;
    private bool $receiveEmailOnNewMessage = true;
    private bool $receiveEmailOnNewLead = true;
    private bool $postCheckinsToChat = true;
    private bool $customPrimaryGoals = false;
    private bool $checkInMessageMandatory = false;
    private bool $askForPeriod = true;
    private bool $showFatPercentage = true;
    private bool $showLeadUtm = false;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /** @return array<string, mixed> */
    public function serialize(): array
    {
        return [
            'profilePicture' => $this->profilePicture,
            'video' => $this->video,
            'welcomeMessage' => $this->welcomeMessage,
            'companyName' => $this->companyName,
            'companyLogo' => $this->companyLogo,
            'showLeadUtm' => $this->showLeadUtm,
            'defaultCurrency' => $this->defaultCurrency,
            'defaultRecurring' => $this->defaultRecurring,
            'defaultMonths' => $this->defaultMonths,
            'defaultUpfront' => $this->defaultUpfront,
            'defaultCheckInDay' => $this->defaultCheckInDay,
            'receiveEmailOnNewMessage' => $this->receiveEmailOnNewMessage,
            'receiveEmailOnNewLead' => $this->receiveEmailOnNewLead,
            'primaryColor' => $this->primaryColor
        ];
    }

    public function isAutoDeactivate(): bool
    {
        return $this->autoDeactivate;
    }

    public function setAutoDeactivate(bool $autoDeactivate): self
    {
        $this->autoDeactivate = $autoDeactivate;
        return $this;
    }

    public function getMailChimpApiKey(): ?string
    {
        return $this->mailChimpApiKey;
    }

    public function setMailChimpApiKey(?string $value): self
    {
        $this->mailChimpApiKey = $value;
        return $this;
    }

    public function getMailChimpListId(): ?string
    {
        return $this->mailChimpListId;
    }

    public function setMailChimpListId(?string $value): self
    {
        $this->mailChimpListId = $value;
        return $this;
    }

    public function setProfilePicture(?string $profilePicture = null): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setVideo(?string $video = null): self
    {
        $this->video = $video;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideoThumb(?string $videoThumb = null): self
    {
        $this->videoThumb = $videoThumb;
        return $this;
    }

    public function getVideoThumb(): ?string
    {
        return $this->videoThumb;
    }

    public function setWelcomeMessage(?string $welcomeMessage = null): self
    {
        $this->welcomeMessage = $welcomeMessage;
        return $this;
    }

    public function getWelcomeMessage(): ?string
    {
        return $this->welcomeMessage;
    }

    public function setCompanyName(?string $companyName = null): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setAddress(?string $address = null): self
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setCompanyLogo(?string $companyLogo = null): self
    {
        $this->companyLogo = $companyLogo;
        return $this;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
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

    public function setReceiveEmailOnNewMessage(bool $receiveEmailOnNewMessage): self
    {
        $this->receiveEmailOnNewMessage = $receiveEmailOnNewMessage;
        return $this;
    }

    public function getReceiveEmailOnNewMessage(): bool
    {
        return $this->receiveEmailOnNewMessage;
    }

    public function setReceiveEmailOnNewLead(bool $receiveEmailOnNewLead): self
    {
        $this->receiveEmailOnNewLead = $receiveEmailOnNewLead;
        return $this;
    }

    public function getReceiveEmailOnNewLead(): bool
    {
        return $this->receiveEmailOnNewLead;
    }

    public function setPostCheckinsToChat(bool $postCheckinsToChat): self
    {
        $this->postCheckinsToChat = $postCheckinsToChat;
        return $this;
    }

    public function getPostCheckinsToChat(): bool
    {
        return $this->postCheckinsToChat;
    }

    public function setCheckInQuestions(?string $checkInQuestions = null): self
    {
        $this->checkInQuestions = $checkInQuestions;
        return $this;
    }

    public function getCheckInQuestions(): ?string
    {
        return $this->checkInQuestions;
    }

    public function setBackgroundImage(?string $backgroundImage = null): self
    {
        $this->backgroundImage = $backgroundImage;
        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function getCustomPrimaryGoals(): bool
    {
        return $this->customPrimaryGoals;
    }

    public function getOldChatsInterval(): int
    {
        return $this->oldChatsInterval;
    }

    public function getCheckInDuration(): int
    {
        return $this->checkInDuration;
    }

    public function getCheckInMessageMandatory(): bool
    {
        return $this->checkInMessageMandatory;
    }

    public function getAskForPeriod(): bool
    {
        return $this->askForPeriod;
    }

    public function getShowFatPercentage(): bool
    {
        return $this->showFatPercentage;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor = null): self
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function showLeadUtm(): bool
    {
        return $this->showLeadUtm;
    }

    public function getDefaultCurrency(): ?string
    {
        return $this->defaultCurrency;
    }

    public function getQuestionnaireText(): ?string
    {
        return $this->questionnaireText;
    }

    public function getDefaultMonths(): ?int
    {
        return $this->defaultMonths;
    }

    public function getDefaultRecurring(): ?int
    {
        return $this->defaultRecurring;
    }

    public function getDefaultUpfront(): ?int
    {
        return $this->defaultUpfront;
    }

    public function getDefaultCheckInDay(): ?int
    {
        return $this->defaultCheckInDay;
    }
}
