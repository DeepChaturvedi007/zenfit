<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientStripe
 *
 * @ORM\Table(name="client_stripe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ClientStripeRepository")
 */
class ClientStripe
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Client
     *
     * @ORM\Column(name="client", type="integer", unique=true)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_customer", type="string", length=255)
     */
    private $stripeCustomer;

    /** @ORM\Column(name="stripe_plan", type="string", length=255) */
    private ?string $stripePlan = null;

    /** @ORM\Column(name="stripe_subscription", type="string", length=255) */
    private ?string $stripeSubscription = null;

    /** @ORM\Column(name="stripe_upfront_charge", type="string", length=255) */
    private ?string $stripeUpfrontCharge = null;

    /** @ORM\Column(name="current_period_start", type="string", length=255) */
    private ?string $currentPeriodStart = null;

    /** @ORM\Column(name="current_period_end", type="string", length=255) */
    private ?string $currentPeriodEnd = null;

    /** @ORM\Column(name="canceled", type="boolean", length=1,  options={"default" : 0}) */
    private bool $canceled = false;

    private ?string $canceledAt = null;

    /** @ORM\Column(name="period_end", type="string", length=255) */
    private ?string $periodEnd = null;

    public function getPeriodEnd(): ?string
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(?string $periodEnd): self
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function getCurrentPeriodStart(): ?string
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(?string $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?string
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?string $currentPeriodEnd): self
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function getStripeUpfrontCharge(): ?string
    {
        return $this->stripeUpfrontCharge;
    }

    public function setStripeUpfrontCharge(?string $stripeUpfrontCharge): self
    {
        $this->stripeUpfrontCharge = $stripeUpfrontCharge;

        return $this;
    }

    public function getStripeSubscription(): ?string
    {
        return $this->stripeSubscription;
    }

    public function setStripeSubscription(?string $stripeSubscription): self
    {
        $this->stripeSubscription = $stripeSubscription;

        return $this;
    }

    public function getStripePlan(): ?string
    {
        return $this->stripePlan;
    }

    public function setStripePlan(?string $stripePlan): self
    {
        $this->stripePlan = $stripePlan;

        return $this;
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

    /**
     * Set clientId
     *
     *
     * @return ClientStripe
     */
    public function setClient(Client $client = null)
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
     * @param $canceled
     * @return ClientStripe
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

    public function setCanceledAt(?string $canceledAt): self
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getCanceledAt(): ?string
    {
        return $this->canceledAt;
    }

    /**
     * @var bool
     */
    private $lastPaymentFailed = false;


    /**
     * Set lastPaymentFailed.
     *
     * @param bool $lastPaymentFailed
     *
     * @return ClientStripe
     */
    public function setLastPaymentFailed($lastPaymentFailed)
    {
        $this->lastPaymentFailed = $lastPaymentFailed;

        return $this;
    }

    /**
     * Get lastPaymentFailed.
     *
     * @return bool
     */
    public function getLastPaymentFailed()
    {
        return $this->lastPaymentFailed;
    }

    private ?Payment $payment = null;

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * Set stripeCustomer.
     *
     * @param string $stripeCustomer
     *
     * @return ClientStripe
     */
    public function setStripeCustomer($stripeCustomer)
    {
        $this->stripeCustomer = $stripeCustomer;

        return $this;
    }

    /**
     * Get stripeCustomer.
     *
     * @return string
     */
    public function getStripeCustomer()
    {
        return $this->stripeCustomer;
    }
    /**
     * @var string|null
     */
    private $nextPaymentAttempt;

    /**
     * @var int|null
     */
    private $attemptCount;


    /**
     * Set nextPaymentAttempt.
     *
     * @param string|null $nextPaymentAttempt
     *
     * @return ClientStripe
     */
    public function setNextPaymentAttempt($nextPaymentAttempt = null)
    {
        $this->nextPaymentAttempt = $nextPaymentAttempt;

        return $this;
    }

    /**
     * Get nextPaymentAttempt.
     *
     * @return string|null
     */
    public function getNextPaymentAttempt()
    {
        return $this->nextPaymentAttempt;
    }

    /**
     * Set attemptCount.
     *
     * @param int|null $attemptCount
     *
     * @return ClientStripe
     */
    public function setAttemptCount($attemptCount = null)
    {
        $this->attemptCount = $attemptCount;

        return $this;
    }

    /**
     * Get attemptCount.
     *
     * @return int|null
     */
    public function getAttemptCount()
    {
        return $this->attemptCount;
    }
    /**
     * @var string|null
     */
    private $invoiceUrl;


    /**
     * Set invoiceUrl.
     *
     * @param string|null $invoiceUrl
     *
     * @return ClientStripe
     */
    public function setInvoiceUrl($invoiceUrl = null)
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    /**
     * Get invoiceUrl.
     *
     * @return string|null
     */
    public function getInvoiceUrl()
    {
        return $this->invoiceUrl;
    }

    private bool $paused = false;

    public function setPaused(bool $paused): self
    {
        $this->paused = $paused;

        return $this;
    }

    public function getPaused(): bool
    {
        return $this->paused;
    }

    private ?string $pausedUntil = null;

    public function setPausedUntil(?string $pausedUntil): self
    {
        $this->pausedUntil = $pausedUntil;

        return $this;
    }

    public function getPausedUntil(): ?string
    {
        return $this->pausedUntil;
    }

    /**
     * @var integer
     */
    private $paymentWarningCount = 0;

    /**
     * @param integer $paymentWarningCount
     */
    public function setPaymentWarningCount($paymentWarningCount)
    {
        $this->paymentWarningCount = $paymentWarningCount;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPaymentWarningCount()
    {
        return $this->paymentWarningCount;
    }

    private ?\DateTime $lastPaymentWarningDate = null;

    public function setLastPaymentWarningDate(?\DateTime $lastPaymentWarningDate): self
    {
        $this->lastPaymentWarningDate = $lastPaymentWarningDate;

        return $this;
    }

    public function getLastPaymentWarningDate(): ?\DateTime
    {
        return $this->lastPaymentWarningDate;
    }
}
