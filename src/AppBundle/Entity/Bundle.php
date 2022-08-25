<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Bundle
{
    public const TYPE_WORKOUT_PLAN = 1;
    public const TYPE_MEAL_PLAN = 2;
    public const TYPE_BOTH = 3;
    public const TYPE_ONLINE_CLIENT = 4;

    public const BUNDLE_TYPE = [
        self::TYPE_WORKOUT_PLAN => 'Workout Plan',
        self::TYPE_MEAL_PLAN => 'Meal Plan',
        self::TYPE_BOTH => 'Workout + Meal Plan'
    ];

    private ?int $id = null;
    private string $name;
    private ?string $description = null;
    private User $user;
    /** @var Collection<int, Lead> */
    private Collection $lead;
    private int $type = 0;
    private int $months = 0;
    private float $recurringFee = 0;
    private bool $trainerNeedsToCreate = false;
    /** @var Collection<int, Document> */
    private Collection $documents;
    private float $upfrontFee = 0;
    private string $currency = 'usd';
    private ?string $terms = null;

    public function __construct(User $user, string $name)
    {
        $this->user = $user;
        $this->name = $name;
        $this->documents = new ArrayCollection();
        $this->lead = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Bundle
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set user
     *
     *
     * @return Bundle
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUpfrontFee(int|float|string $upfrontFee): self
    {
        $this->upfrontFee = (float) $upfrontFee;

        return $this;
    }

    public function getUpfrontFee(): float
    {
        return $this->upfrontFee;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return Bundle
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Add document
     *
     *
     * @return Bundle
     */
    public function addDocument(\AppBundle\Entity\Document $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * Remove document
     */
    public function removeDocument(\AppBundle\Entity\Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    public function setRecurringFee(int|float|string $recurringFee): self
    {
        $this->recurringFee = (float) $recurringFee;

        return $this;
    }

    public function getRecurringFee(): float
    {
        return $this->recurringFee;
    }

    public function setMonths(int $months): self
    {
        $this->months = $months;

        return $this;
    }

    public function getMonths(): int
    {
        return $this->months;
    }


    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Bundle
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Set trainerNeedsToCreate.
     *
     * @param bool $trainerNeedsToCreate
     *
     * @return Bundle
     */
    public function setTrainerNeedsToCreate($trainerNeedsToCreate)
    {
        $this->trainerNeedsToCreate = $trainerNeedsToCreate;

        return $this;
    }

    /**
     * Get trainerNeedsToCreate.
     *
     * @return bool
     */
    public function getTrainerNeedsToCreate()
    {
        return $this->trainerNeedsToCreate;
    }

    /**
     * @return Collection
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @return $this
     */
    public function setLead(Collection $lead)
    {
        $this->lead = $lead;
        return $this;
    }

    /**
     * Add lead.
     *
     *
     * @return Bundle
     */
    public function addLead(\AppBundle\Entity\Lead $lead)
    {
        $this->lead[] = $lead;

        return $this;
    }

    /**
     * Remove lead.
     *
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLead(\AppBundle\Entity\Lead $lead)
    {
        return $this->lead->removeElement($lead);
    }

    public function setTerms(?string $terms): self
    {
        $this->terms = $terms;

        return $this;
    }

    public function getTerms(): ?string
    {
        return $this->terms;
    }
}
