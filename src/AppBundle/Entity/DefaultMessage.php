<?php

namespace AppBundle\Entity;

/**
 * DefaultMessage
 */
class DefaultMessage
{
    const TYPE_PAYMENT_MESSAGE_EMAIL = 1;
    const TYPE_PDF_MEAL_PLANS_EMAIL = 2;
    const TYPE_PDF_MEAL_PLANS_INTRO = 3;
    const TYPE_WELCOME_EMAIL = 4;
    const TYPE_PLANS_READY_EMAIL = 5;
    const TYPE_BOUNCED_LEAD_EMAIL = 6;
    const TYPE_BODY_PROGRESS_FEEDBACK = 7;
    const TYPE_PAYMENT_FAILED_MESSAGE = 8;
    const TYPE_CLIENT_ENDING_MESSAGE = 9;
    const TYPE_UPDATED_WORKOUT = 10;
    const TYPE_UPDATED_MEAL = 11;
    const TYPE_MISSING_CHECKIN = 12;
    const TYPE_WELCOME_MESSAGE = 13;
    const TYPE_CHECKOUT_TERMS = 14;
    const TYPE_CHAT_MESSAGE_PROGRESS = 15;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $message;

    /**
     * @var integer
     */
    private $type;

    private ?User $user = null;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return DefaultMessage
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return DefaultMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    private ?string $title = null;

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    /**
     * @var string|null
     */
    private $subject;


    /**
     * Set subject.
     *
     * @param string|null $subject
     *
     * @return DefaultMessage
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @var string|null
     */
    private $locale = 'en';


    /**
     * Set locale.
     *
     * @param string|null $locale
     *
     * @return Client
     */
    public function setLocale($locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    private bool $autoAssign = false;

    public function getAutoAssign(): bool
    {
        return $this->autoAssign;
    }

    /**
     * @return int[]
     */
    public static function getTypesMap(): array
    {
        return [
            self::TYPE_PAYMENT_MESSAGE_EMAIL,
            self::TYPE_PDF_MEAL_PLANS_EMAIL,
            self::TYPE_PDF_MEAL_PLANS_INTRO,
            self::TYPE_WELCOME_EMAIL,
            self::TYPE_PLANS_READY_EMAIL,
            self::TYPE_BOUNCED_LEAD_EMAIL,
            self::TYPE_BODY_PROGRESS_FEEDBACK,
            self::TYPE_PAYMENT_FAILED_MESSAGE,
            self::TYPE_CLIENT_ENDING_MESSAGE,
            self::TYPE_UPDATED_WORKOUT,
            self::TYPE_UPDATED_MEAL,
            self::TYPE_MISSING_CHECKIN,
            self::TYPE_WELCOME_MESSAGE,
            self::TYPE_CHECKOUT_TERMS,
            self::TYPE_CHAT_MESSAGE_PROGRESS,
        ];
    }
}
