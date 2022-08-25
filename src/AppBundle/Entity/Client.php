<?php declare(strict_types=1);

namespace AppBundle\Entity;

use ChatBundle\Entity\Conversation;
use ChatBundle\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Client
{
    private ?int $id = null;
    private string $name;
    private string $email;
    private ?string $password = null;
    private ?string $phone = null;
    private ?string $injuries = null;
    private ?string $other = null;
    private ?string $token = null;
    private ?string $photo = null;
    private ?string $experience = null;
    private ?string $exercisePreferences = null;
    private ?float $startWeight = null;
    private ?float $pal = null;
    private ?int $dayTrackProgress = null;
    private int $measuringSystem = self::MEASURING_SYSTEM_METRIC;
    private ?int $age = null;
    private ?float $height = null;
    private ?float $feet = null;
    private ?float $inches = null;
    private ?string $lifestyle = null;
    private ?int $activityLevel = null;
    private ?int $gender = null;
    private ?int $primaryGoal = null;
    private ?string $motivation = null;
    private ?float $goalWeight = null;
    private ?string $dietStyle = null;
    private ?int $duration = null;
    private ?string $locale = 'en';
    private ?\DateTime $endDate = null;
    private ?string $note = null;
    private ?int $workoutsPerWeek = null;
    private ?int $experienceLevel = null;
    private ?int $numberOfMeals = null;
    private ?int $workoutLocation = null;

    private ?int $updateWorkoutSchedule = 4;
    private ?int $updateMealSchedule = 4;

    private ?\DateTime $createdAt = null;
    private ?\DateTime $workoutUpdated = null;
    private ?\DateTime $mealUpdated = null;
    private ?\DateTime $bodyProgressUpdated = null;
    private ?\DateTime $deletedAt = null;
    private ?\DateTime $startDate = null;

    private bool $deleted = false;
    private bool $acceptTerms = false;
    private bool $acceptEmailNotifications = false;
    private bool $answeredQuestionnaire = false;
    private bool $accessApp = true;
    private bool $active = true;
    private bool $demoClient = false;
    private bool $lasseDemoClient = false;

    /** @var Collection<int, ClientImage> */
    private Collection $images;
    /** @var Collection<int, ClientReminder> */
    private Collection $reminders;
    /** @var Collection<int, ProgressFeedback> */
    private Collection $checkIns;
    /** @var Collection<int, Queue> */
    private Collection $emails;
    /** @var Collection<int, Message> */
    private Collection $messages;
    /** @var Collection<int, Conversation> */
    private Collection $conversations;
    /** @var Collection<int, Payment> */
    private Collection $payments;
    /** @var Collection<int, PaymentsLog> */
    private Collection $paymentsLog;
    /** @var Collection<int, BodyProgress> */
    private Collection $bodyProgress;
    /** @var Collection<int, WorkoutPlan> */
    private Collection $workoutPlans;
    /** @var Collection<int, MealPlan> */
    private Collection $mealPlans;
    /** @var Collection<int, VideoClient> */
    private Collection $videoClient;
    /** @var Collection<int, Answer> */
    private Collection $answers;
    /** @var Collection<int, ActivityLog> */
    private Collection $activities;
    /** @var Collection<int, MasterMealPlan> */
    private Collection $masterMealPlans;
    /** @var Collection<int, ClientTag> */
    private Collection $tags;
    /** @var Collection<int, ClientStatus> */
    private Collection $clientStatus;

    private User $user;
    private ?ClientStripe $clientStripe = null;
    private ?ClientSettings $clientSettings = null;
    private ?ClientFoodPreference $clientFoodPreferences = null;
    private ?Plan $plan = null;
    private ?Lead $lead = null;

    public function __construct(User $user, string $name, string $email)
    {
        $this->user = $user;
        $this->name = $name;
        $this->email = $email;
        $this->workoutPlans = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->reminders = new ArrayCollection();
        $this->checkIns = new ArrayCollection();
        $this->bodyProgress = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->emails = new ArrayCollection();
        $this->mealPlans = new ArrayCollection();
        $this->masterMealPlans = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->paymentsLog = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->clientStatus = new ArrayCollection();
        $this->videoClient = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    const NOTIFICATION_DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    const UPDATE_PLANS_SCHEDULE = [
        4 => 'Every 4 weeks',
        6 => 'Every 6 weeks',
        8 => 'Every 8 weeks'
    ];

  	const DURATION_MONTHS = [
        0 => 'No end date',
        1 => '1 Month',
        2 => '2 Months',
        3 => '3 Months',
        4 => '4 Months',
        5 => '5 Months',
        6 => '6 Months',
        7 => '7 Months',
        8 => '8 Months',
        9 => '9 Months',
        10 => '10 Months',
        11 => '11 Months',
        12 => '12 Months'
  	];

    const MEASURING_SYSTEM_METRIC = 1;
    const MEASURING_SYSTEM_IMPERIAL = 2;
    const MEASURING_SYSTEM_COEFICIENT = 0.45359237;
    const LENGTH_SYSTEM_COEFICIENT = 2.54;

    const MEASURING_SYSTEM = [
        1 => 'kg',
        2 => 'lbs'
    ];

    const ACTIVITY_LEVEL_TO_PAL = [
        1 => 1.3,
        2 => 1.6,
        3 => 1.8,
        4 => 2.0,
        5 => 2.2
    ];

    const GENDER_MALE = 2;
    const GENDER_FEMALE = 1;

    const AWS_PHOTO_KEY = 'client/photo';

    const GOAL_TYPE_LOSE_WEIGHT = 1;
    const GOAL_TYPE_GAIN_WEIGHT = 2;

    /** @return array<string, mixed> */
    public function getClientInfo(): array
    {
        $lead = $this->getLead();
        $clientFoodPreferences = $this->getClientFoodPreferences();

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'photo' => $this->getPhoto(),
            'height' => $this->getHeight(),
            'feet' => $this->getFeet(),
            'inches' => $this->getInches(),
            'injuries' => $this->getInjuries(),
            'experience' => $this->getExperience(),
            'experienceLevel' => $this->getExperienceLevel(),
            'other' => $this->getOther(),
            'age' => $this->getAge(),
            'locale' => $this->getLocale(),
            'lifestyle' => $this->getLifestyle(),
            'gender' => $this->getGender(),
            'motivation' => $this->getMotivation(),
            'activityLevel' => $this->getActivityLevel(),
            'dietStyle' => $this->getDietStyle(),
            'workoutsPerWeek' => $this->getWorkoutsPerWeek(),
            'numberOfMeals' => $this->getNumberOfMeals(),
            'workoutLocation' => $this->getWorkoutLocation(),
            'updateWorkoutSchedule' => $this->getUpdateWorkoutSchedule(),
            'updateMealSchedule' => $this->getUpdateMealSchedule(),
            'measuringSystem' => $this->getMeasuringSystem(),
            'goalType' => $this->getGoalType(),
            'goalWeight' => $this->getGoalWeight(),
            'startWeight' => $this->getStartWeight(),
            'clientFoodPreferences' => $clientFoodPreferences !== null ? $clientFoodPreferences->prepareData() : [],
            'exercisePreferences' => $this->getExercisePreferences(),
            'pal' => $this->getPal(),
            'isActive' => $this->isActive(),
            'notes' => [
                'note' => $this->getNote(),
                'salesNotes' => $lead !== null ? $lead->getSalesNotes() : null,
                'dialogMessage' => $lead !== null ? $lead->getDialogMessage() : null,
            ]
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $hashedPassword = password_hash($password,PASSWORD_BCRYPT);
        if (!is_string($hashedPassword)) {
            throw new \RuntimeException('Could not hash a password');
        }
        $this->password = $hashedPassword;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setPhone(?string $phone = null): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setCreatedAt(?\DateTime $createdAt = null): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setWorkoutUpdated(?\DateTime $datetime = null): self
    {
        $this->workoutUpdated = $datetime;
        return $this;
    }

    public function getWorkoutUpdated(): ?\DateTime
    {
        return $this->workoutUpdated;
    }

    public function setInjuries(?string $injuries = null): self
    {
        $this->injuries = $injuries;
        return $this;
    }

    public function getInjuries(): ?string
    {
        return $this->injuries;
    }

    public function setExperience(?string $experience = null): self
    {
        $this->experience = $experience;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperienceLevel(?int $experienceLevel = null): self
    {
        $this->experienceLevel = $experienceLevel;
        return $this;
    }

    public function getExperienceLevel(): ?int
    {
        return $this->experienceLevel;
    }

    public function setOther(?string $other = null): self
    {
        $this->other = $other;
        return $this;
    }

    public function getOther(): ?string
    {
        return $this->other;
    }

    public function setAge(?int $age = null): self
    {
        $this->age = $age;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setExercisePreferences(?string $exercisePreferences = null): self
    {
        $this->exercisePreferences = $exercisePreferences;
        return $this;
    }

    public function getExercisePreferences(): ?string
    {
        return $this->exercisePreferences;
    }

    public function setStartWeight(?float $startWeight = null): self
    {
        $this->startWeight = $startWeight;
        return $this;
    }

    public function getStartWeight(): ?float
    {
        return $this->startWeight;
    }

    public function setPal(?float $pal = null): self
    {
        $this->pal = $pal;
        return $this;
    }

    public function getPal(): ?float
    {
        if ($this->pal === null && $this->getActivityLevel() !== null) {
            $this->pal = self::ACTIVITY_LEVEL_TO_PAL[$this->getActivityLevel()] ?? null;
        }

        return $this->pal;
    }

    public function setHeight(?float $height = null): self
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setFeet(?float $feet = null): self
    {
        $this->feet = $feet;
        return $this;
    }

    public function getFeet(): ?float
    {
        return $this->feet;
    }

    public function setInches(?float $inches = null): self
    {
        $this->inches = $inches;
        return $this;
    }

    public function getInches(): ?float
    {
        return $this->inches;
    }

    public function setLifestyle(?string $lifestyle = null): self
    {
        $this->lifestyle = $lifestyle;
        return $this;
    }

    public function getLifestyle(): ?string
    {
        return $this->lifestyle;
    }

    /** @return Collection<int, BodyProgress> */
    public function getBodyProgress(): Collection
    {
        return $this->bodyProgress;
    }

    public function getLatestBodyProgress(): ?BodyProgress
    {
        return $this->getBodyProgress() !== null && $this->getBodyProgress()->last() !== false ? $this->getBodyProgress()->last() : null;
    }

    public function getFirstName(): ?string
    {
        $fullName = $this->getName();
        return explode(" ", $fullName)[0];
    }

    /** @return mixed[] */
    public function getQuestionnaireSettings(string $appHostname): array
    {
        $questionnaireExists = $this
            ->getClientStatus()
            ->filter(function($clientStatus) {
                return $clientStatus->getEvent()->getName() === Event::QUESTIONNAIRE_PENDING;
            });

        $shouldAnswer = $questionnaireExists->count() > 0;
        $hasAnswered = false;
        if ($shouldAnswer) {
            $hasAnswered = $questionnaireExists->last()->getResolved();
        }

        return [
            'shouldAnswer' => $shouldAnswer,
            'hasAnswered' => $hasAnswered,
            'url' => $this->getClientURL(true, $appHostname)
        ];
    }

    /** @return string[] */
    public function tagsList(): array
    {
        return array_values(array_unique(array_map(function(ClientTag $tag) {
            return $tag->getTitle();
        }, $this->getTags()->toArray())));
    }

    public function getClientURL(bool $survey, string $appHostname): ?string
    {
        $queue = $this->getEmails()
            ->filter(function($entry) {
                /* @var Queue $entry */
                return $entry->getType() == Queue::TYPE_CLIENT_EMAIL;
            })
            ->last();

        if ($queue) {
            if ($survey) {
                return $queue->getQuestionnaireSurveyOnlyUrl($appHostname);
            } else {
                return $queue->getClientCreationLink($appHostname);
            }
        }

        return null;
    }

    public function hasBeenActivated(): bool
    {
        $needWelcome = $this
            ->getClientStatus()
            ->filter(function($clientStatus) {
                return !$clientStatus->getResolved();
            })->map(function($clientStatus) {
                return $clientStatus->getEvent()->getName();
            })->contains(Event::NEED_WELCOME);

        if ($needWelcome) {
            return false;
        }

        return true;
    }

    /** @return array<Answer>|Answer[] */
    public function getAnswers(bool $asObj = false): array
    {
        if ($asObj) {
            return collect($this->answers)->map(function(Answer $a) {
                return [
                    'id' => $a->getId(),
                    'answer' => $a->getAnswer(),
                    'questionId' => $a->getQuestion()->getId()
                ];
            })->toArray();
        }

        return $this->answers->toArray();
    }

    public function addAnswer(Answer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
        }
    }

    public function onPrePersist(): void
    {
        $this->setCreatedAt(new \DateTime('now'));
    }

    public function onPreUpdate(): void
    {
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /** @return Collection<int, WorkoutPlan> */
    public function getWorkoutPlans(): Collection
    {
        return $this->workoutPlans;
    }

    public function getDemoClient(): bool
    {
        return $this->demoClient;
    }

    public function getLasseDemoClient(): bool
    {
        return $this->lasseDemoClient;
    }

    public function setDemoClient(bool $demoClient): self
    {
        $this->demoClient = $demoClient;
        return $this;
    }

    public function setLasseDemoClient(bool $lasseDemoClient): self
    {
        $this->lasseDemoClient = $lasseDemoClient;
        return $this;
    }

    public function isDemo(): bool
    {
        return $this->getDemoClient();
    }

    public function isActive(): bool
    {
        return !empty($this->password);
    }

    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function getCheckIns(): Collection
    {
        return $this->checkIns;
    }

    /** @return Collection<int, Queue> */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    public function getMealUpdated(): ?\DateTime
    {
        return $this->mealUpdated;
    }

    public function setMealUpdated(?\DateTime $mealUpdated = null): self
    {
        $this->mealUpdated = $mealUpdated;
        return $this;
    }

    public function setGender(?int $gender = null): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function isMale(): bool
    {
        return $this->gender === self::GENDER_MALE;
    }

    public function isFemale(): bool
    {
        return $this->gender === self::GENDER_FEMALE;
    }

    public function setPrimaryGoal(?int $primaryGoal = null): self
    {
        $this->primaryGoal = $primaryGoal;
        return $this;
    }

    public function getPrimaryGoal(): ?int
    {
        return $this->primaryGoal;
    }

    public function setMotivation(?string $motivation = null): self
    {
        $this->motivation = $motivation;
        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        $deleted ? $this->setAccessApp(false) : $this->setAccessApp(true);

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setDayTrackProgress(?int $dayTrackProgress = null): self
    {
        $this->dayTrackProgress = $dayTrackProgress;
        return $this;
    }

    public function getDayTrackProgress(): ?int
    {
        return $this->dayTrackProgress;
    }

    public function setStartDate(?\DateTime $startDate = null): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setAnsweredQuestionnaire(bool $answeredQuestionnaire): self
    {
        $this->answeredQuestionnaire = $answeredQuestionnaire;
        return $this;
    }

    public function getAnsweredQuestionnaire(): bool
    {
        return $this->answeredQuestionnaire;
    }

    public function setPhoto(?string $photo = null): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setMeasuringSystem(?int $measuringSystem = null): self
    {
        if ($measuringSystem === null) {
            $measuringSystem = self::MEASURING_SYSTEM_METRIC;
        }
        $this->measuringSystem = $measuringSystem;
        return $this;
    }

    public function getMeasuringSystem(): int
    {
        return $this->measuringSystem;
    }

    public function isMetricMeasuringSystem(): bool
    {
        return $this->measuringSystem === self::MEASURING_SYSTEM_METRIC;
    }

    public function isImperialMeasuringSystem(): bool
    {
        return $this->measuringSystem === self::MEASURING_SYSTEM_IMPERIAL;
    }

    public function setActivityLevel(?int $activityLevel = null): self
    {
        $this->activityLevel = $activityLevel;
        return $this;
    }

    public function getActivityLevel(): ?int
    {
        return $this->activityLevel;
    }

    public function setGoalWeight(?float $goalWeight = null): self
    {
        $this->goalWeight = $goalWeight;
        return $this;
    }

    public function getGoalWeight(): ?float
    {
        return $this->goalWeight;
    }

    public function setDietStyle(?string $dietStyle = null): self
    {
        $this->dietStyle = $dietStyle;
        return $this;
    }

    public function getDietStyle(): ?string
    {
        return $this->dietStyle;
    }

    public function setDuration(?int $duration = null): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setBodyProgressUpdated(?\DateTime $bodyProgressUpdated = null): self
    {
        $this->bodyProgressUpdated = $bodyProgressUpdated;
        return $this;
    }

    public function getBodyProgressUpdated(): ?\DateTime
    {
        return $this->bodyProgressUpdated;
    }

    public function setAcceptTerms(bool $acceptTerms): self
    {
        $this->acceptTerms = $acceptTerms;
        return $this;
    }

    public function getAcceptTerms(): bool
    {
        return $this->acceptTerms;
    }

    public function setAcceptEmailNotifications(bool $acceptEmailNotifications): self
    {
        $this->acceptEmailNotifications = $acceptEmailNotifications;
        return $this;
    }

    public function getAcceptEmailNotifications(): bool
    {
        return $this->acceptEmailNotifications;
    }

    /** @return Collection<int, Message>|Message[] */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /** @return Collection|Conversation[] */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    /** @return Collection|PaymentsLog[] */
    public function getPaymentsLog(): Collection
    {
        return $this->paymentsLog;
    }

    /** @return Collection|Payment[] */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getClientStripe(): ?ClientStripe
    {
        return $this->clientStripe;
    }

    public function setLead(?Lead $lead): self
    {
        $this->lead = $lead;
        return $this;
    }

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function getGoalType(): ?int
    {
        return $this->getGoalWeight() < $this->getStartWeight() ? self::GOAL_TYPE_LOSE_WEIGHT : self::GOAL_TYPE_GAIN_WEIGHT;
    }

    public function setAccessApp(bool $accessApp): self
    {
        $this->accessApp = $accessApp;
        return $this;
    }

    public function getAccessApp(): bool
    {
        return $this->accessApp;
    }

    public function getClientFoodPreferences(): ?ClientFoodPreference
    {
        return $this->clientFoodPreferences;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return Collection<mixed, ClientTag>|ClientTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /** @return Collection|VideoClient[] */
    public function getVideoClient(): Collection
    {
        return $this->videoClient;
    }

    /** @return Collection|ClientStatus[] */
    public function getClientStatus(): Collection
    {
        return $this->clientStatus;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?: 'en';
    }

    public function setEndDate(?\DateTime $endDate = null): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setNote(?string $note = null): self
    {
        $this->note = $note;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getClientSettings(): ?ClientSettings
    {
        return $this->clientSettings;
    }

    public function setWorkoutsPerWeek(?int $workoutsPerWeek = null): self
    {
        $this->workoutsPerWeek = $workoutsPerWeek;
        return $this;
    }

    public function getWorkoutsPerWeek(): ?int
    {
        return $this->workoutsPerWeek;
    }

    public function setNumberOfMeals(?int $numberOfMeals = null): self
    {
        $this->numberOfMeals = $numberOfMeals;
        return $this;
    }

    public function getNumberOfMeals(): ?int
    {
        return $this->numberOfMeals;
    }

    public function setWorkoutLocation(?int $workoutLocation = null): self
    {
        $this->workoutLocation = $workoutLocation;
        return $this;
    }

    public function getWorkoutLocation(): ?int
    {
        return $this->workoutLocation;
    }

    public function setPlan(?Plan $plan = null): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setUpdateWorkoutSchedule(?int $updateWorkoutSchedule = null): self
    {
        $this->updateWorkoutSchedule = $updateWorkoutSchedule;
        return $this;
    }

    public function getUpdateWorkoutSchedule(): ?int
    {
        return $this->updateWorkoutSchedule;
    }

    public function setUpdateMealSchedule(?int $updateMealSchedule = null): self
    {
        $this->updateMealSchedule = $updateMealSchedule;
        return $this;
    }

    public function getUpdateMealSchedule(): ?int
    {
        return $this->updateMealSchedule;
    }
}
