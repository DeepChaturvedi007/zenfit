<?php declare(strict_types=1);

namespace AppBundle\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Illuminate\Support\Collection as LaravelCollection;
use FOS\UserBundle\Model\User as BaseUser;
use GymBundle\Entity\Gym;
use ChatBundle\Entity\Message;
use ChatBundle\Entity\Conversation;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use function count;

class User extends BaseUser implements PasswordAuthenticatedUserInterface
{
    public const USER_TYPE_TRAINER = 1;
    public const USER_TYPE_ASSISTANT = 2;
    public const ROLE_TRAINER = 'ROLE_TRAINER';
    public const ROLE_ASSISTANT = 'ROLE_ASSISTANT';

    /** @var Collection<int, Question> */
    private Collection $questions;

    /** @var Collection<int, Video> */
    private Collection $videos;

    /** @var Collection<int, Gym> */
    private Collection $gyms;

    /** @var Collection<int, Client> */
    private Collection $clients;

    /** @var Collection<int, Document> */
    private Collection $documents;

    /** @var Collection<int, MealProduct> */
    private Collection $mealProducts;

    /** @var Collection<int, ActivityLog> */
    private Collection $activities;

    /** @var Collection<int, WorkoutPlan> */
    private Collection $workoutPlans;

    /** @var Collection<int, Conversation> */
    private Collection $conversations;

    /** @var Collection<int, Message> */
    private Collection $messages;

    /** @var Collection<int, Recipe> */
    private Collection $recipes;

    /** @var Collection<int, RecipePreference> */
    private Collection $recipePreferences;

    /** @var Collection<int, MasterMealPlan> */
    private Collection $mealPlans;

    /** @var Collection<int, DefaultMessage> */
    private Collection $defaultMessages;

    /** @var Collection<int, Lead> */
    private Collection $leads;

    /** @var Collection<int, StripeConnect> */
    private Collection $stripeConnect;

    private ?Language $language = null;
    private ?UserStripe $userStripe = null;
    private ?UserApp $userApp = null;
    private ?UserTerms $userTerms = null;
    private ?UserSubscription $userSubscription = null;
    private ?UserSettings $userSettings = null;

    private int $userType = self::USER_TYPE_TRAINER;
    private string $name = '';
    private \DateTime $signupDate;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $interactiveToken = null;
    private ?string $phone = null;
    private ?float $monthlyGoal = null;

    private bool $activated = false;
    private bool $plansVisible = false;
    private bool $leadsVisible = true;
    private bool $onlyShowOwnExercises = false;
    private bool $assignLeads = true;
    private bool $hideNutritionalFactsInApp = false;
    private bool $deleted = false;

    public function __construct()
    {
        parent::__construct();
        $this->signupDate = new \DateTime();
        $this->clients = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->mealProducts = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->workoutPlans = new ArrayCollection();
        $this->mealPlans = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->leads = new ArrayCollection();
        $this->defaultMessages = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->recipePreferences = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->gyms = new ArrayCollection();
        $this->stripeConnect = new ArrayCollection();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        $namePieces = explode(' ', $name);
        $this->setFirstName($namePieces[0]);

        if (isset($namePieces[1])) {
            $this->setLastName($namePieces[1]);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSignupDate(\DateTime $signupDate): self
    {
        $this->signupDate = $signupDate;
        return $this;
    }

    public function getSignupDate(): \DateTime
    {
        return $this->signupDate;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setInteractiveToken(?string $interactiveToken = null): self
    {
        $this->interactiveToken = $interactiveToken;
        return $this;
    }

    public function getInteractiveToken(): ?string
    {
        return $this->interactiveToken;
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

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;
        $this->setActivated(false);
        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setHideNutrionalFactsInApp(bool $hideNutritionalFactsInApp = false): self
    {
        $this->hideNutritionalFactsInApp = $hideNutritionalFactsInApp;
        return $this;
    }

    public function getHideNutrionalFactsInApp(): bool
    {
        return $this->hideNutritionalFactsInApp;
    }

    public function setUserStripe(?UserStripe $userStripe): self
    {
        $this->userStripe = $userStripe;
        return $this;
    }

    public function getUserStripe(): ?UserStripe
    {
        return $this->userStripe;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;
        return $this;
    }

    public function getActivated(): bool
    {
        return $this->activated;
    }

    public function getPlansVisible(): bool
    {
        return $this->plansVisible;
    }

    public function getLeadsVisible(): bool
    {
        return $this->leadsVisible;
    }

    public function getUserApp(): ?UserApp
    {
        return $this->userApp;
    }

    public function setOnlyShowOwnExercises(bool $onlyShowOwnExercises = false): self
    {
        $this->onlyShowOwnExercises = $onlyShowOwnExercises;
        return $this;
    }

    public function setUserTerms(UserTerms $userTerms = null): self
    {
        $this->userTerms = $userTerms;
        return $this;
    }

    public function getUserTerms(): ?UserTerms
    {
        return $this->userTerms;
    }

    public function getMonthlyGoal(): ?float
    {
        return $this->monthlyGoal;
    }

    public function setMonthlyGoal(?float $monthlyGoal = null): self
    {
        $this->monthlyGoal = $monthlyGoal;
        return $this;
    }

    public function setUserSubscription(UserSubscription $userSubscription = null): self
    {
        $this->userSubscription = $userSubscription;
        return $this;
    }

    public function getUserSubscription(): ?UserSubscription
    {
        return $this->userSubscription;
    }

    public function getUserType(): int
    {
        return $this->userType;
    }

    public function setUserType(int $value): self
    {
        $this->userType = $value;
        return $this;
    }

    public function isAssistant(): bool
    {
        return $this->getUserType() === self::USER_TYPE_ASSISTANT;
    }

    public function getGymAdmin(): self
    {
        $userGyms = $this->getGyms();
        if (count($userGyms) > 0) {
            if (array_key_exists(0, $userGyms)) {
                return $userGyms[0]->getAdmin();
            }

            throw new  \RuntimeException('No gym admin set');
        }

        throw new \RuntimeException('User has no gyms');
    }

    public function getRoles(): array
    {
        $roles = parent::getRoles();
        if ($this->getUserType() === self::USER_TYPE_ASSISTANT) {
            $roles[] = self::ROLE_ASSISTANT;
        } elseif($this->getUserType() === self::USER_TYPE_TRAINER) {
            $roles[] = self::ROLE_TRAINER;
        }

        return $roles;
    }

    public function getAssignLeads(): bool
    {
        return $this->assignLeads;
    }

    /** @return Video[] */
    public function getVideos(): array
    {
        return $this->videos->toArray();
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getDemoClient(): ?Client
    {
        /** @var Client|false $demoClient */
        $demoClient = $this->clients
            ->filter(fn (Client $client) => $client->getDemoClient())
            ->first();

        if ($demoClient === false) {
            return null;
        }

        return $demoClient;
    }

    /** @return Client[] */
    public function getClients(): array
    {
        return $this->clients->toArray();
    }

    /** @return Document[] */
    public function getDocuments(): array
    {
        return $this->documents->toArray();
    }

    /** @return MealProduct[] */
    public function getMealProducts(): array
    {
        return $this->mealProducts->toArray();
    }

    /** @return WorkoutPlan[] */
    public function getWorkoutPlans(): array
    {
        return $this->workoutPlans->toArray();
    }

    /** @return Conversation[] */
    public function getConversations(): array
    {
        return $this->conversations->toArray();
    }

    /** @return Message[] */
    public function getMessages(): array
    {
        return $this->messages->toArray();
    }

    /** @return MasterMealPlan[] */
    public function getMealPlans(): array
    {
        return $this->mealPlans->toArray();
    }

    /** @return LaravelCollection<int, Lead> */
    public function getLeads(): LaravelCollection
    {
        return collect($this->leads)
            ->filter(
                fn (Lead $lead) => !$lead->getDeleted()
            );
    }

    /** @return DefaultMessage[] */
    public function getDefaultMessages(): array
    {
        return $this->defaultMessages->toArray();
    }

    /** @return Recipe[] */
    public function getRecipes(): array
    {
        return $this->recipes->toArray();
    }

    /** @return RecipePreference[] */
    public function getRecipePreferences(): array
    {
        return $this->recipePreferences->toArray();
    }

    /** @return Gym[] */
    public function getGyms(): array
    {
        return $this->gyms->toArray();
    }

    /** @return StripeConnect[] */
    public function getStripeConnect(): array
    {
        return $this->stripeConnect->toArray();
    }

    public function unreadMessagesCount(?Client $targetClient = null): int
    {
        $newCriteria = Criteria::create()
            ->where(Criteria::expr()->eq('isNew', 1))
            ->andWhere(Criteria::expr()->neq('client', null));

        if ($targetClient !== null) {
            $newCriteria->andWhere(Criteria::expr()->eq('client', $targetClient));
        }

        $count = 0;
        $clients = [];
        /** @var Message[] $messages */
        $messages = $this->messages->matching($newCriteria)->toArray();

        foreach ($messages as $message) {
            $messageClient = $message->getClient();
            if ($messageClient === null) {
                continue;
            }
            $client = $messageClient->getId();
            if ($messageClient->getDeleted()) {
                continue;
            }
            if ($message->getConversation()->getDeleted()) {
                continue;
            }
            if (!in_array($client, $clients, true)) {
                $clients[] = $client;
                $count++;
            }
        }

        return $count;
    }

    public function newLeadsCount(): int
    {
        if ($this->isAssistant()) {
            return 0; //TODO
        }

        $newCriteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', 1))
            ->andWhere(Criteria::expr()->eq('deleted', 0));

        return count($this->leads->matching($newCriteria)->toArray());
    }

    public function getTotalActiveClients(bool $includePending = true): int
    {
        return collect($this->getClients())
            ->filter(function ($client) use ($includePending) {
                if ($includePending) {
                    return !$client->getDeleted() && $client->getActive() && !$client->getDemoClient();
                }

                return !$client->getDeleted()
                    && $client->getActive()
                    && $client->hasBeenActivated()
                    && !$client->getDemoClient();
            })->count();
    }

    public function getEmailName(): string
    {
        $firstName = $this->getFirstName();

        return $firstName ?? $this->getName();
    }

    public function getTrainerName(): string
    {
        $userSettings = $this->getUserSettings();
        if ($userSettings !== null) {
            $companyName = $userSettings->getCompanyName();
            if ($companyName !== null) {
                return $companyName;
            }
        }
        return $this->getName() . ' Fitness';
    }

    /** @return array<Client> */
    public function getActivatedClientsByTag(string $tag): array
    {
        return collect($this->getClients())
            ->filter(function(Client $client) {
                return !$client->getDeleted() && $client->getActive() && $client->hasBeenActivated();
            })
            ->filter(function(Client $client) use ($tag) {
                $clientTags = collect($client->tagsList())
                    ->map(function($clientTag) {
                        return $clientTag['title'];
                    })->toArray();

                return in_array($tag, $clientTags, true);
            })->toArray();
    }

    public function getDefaultMessageByType(int $type, string $locale = null): ?DefaultMessage
    {
        /** @var DefaultMessage|false $lastDefaultMessage */
        $lastDefaultMessage = $this->defaultMessages->filter(function (DefaultMessage $defaultMessage) use ($type, $locale) {
            if ($locale !== null && $defaultMessage->getLocale() !== $locale) {
                return false;
            }

            return $defaultMessage->getType() === $type;
        })->last();


        if ($lastDefaultMessage === false) {
            return null;
        }

        return $lastDefaultMessage;
    }

    public function getNextPaymentAttempt(): ?\DateTimeImmutable
    {
        $subscription = $this->getUserSubscription();

        if ($subscription === null || $subscription->getCanceled()) {
            return null;
        }

        $lastPaymentFailed = $subscription->getLastPaymentFailed();
        $nextPaymentAttempt = (int)$subscription->getNextPaymentAttempt();

        if ($lastPaymentFailed && Carbon::createFromTimestamp($nextPaymentAttempt)->gt(Carbon::now())) {
            return (new \DateTimeImmutable())->setTimestamp($nextPaymentAttempt);
        }

        return null;
    }

    public function getNextInvoiceUrl(): ?string
    {
        return $this->getUserSubscription()?->getInvoiceUrl();
    }

    public function setUserSettings(UserSettings $userSettings): self
    {
        $this->userSettings = $userSettings;
        return $this;
    }

    public function getUserSettings(): ?UserSettings
    {
        if ($this->isAssistant()) {
            return $this->getGymAdmin()->getUserSettings();
        }

        return $this->userSettings;
    }

    /** @return Question[] */
    public function getQuestions(bool $returnObj = true): array
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('deleted', false))
            ->orderBy(['order' => Criteria::ASC]);

        $obj = $this->questions->matching($criteria)->toArray();

        if ($returnObj === true) {
            return $obj;
        }

        return collect($obj)->map(function(Question $q) {
            return [
                'id' => $q->getId(),
                'text' => $q->getText(),
                'subtitle' => $q->getSubtitle()
            ];
        })->toArray();
    }

    /** @return Question[] */
    public function getGymAdminOrOwnQuestions(): array
    {
        try {
            $gymAdmin = $this->getGymAdmin();
            $questions = $gymAdmin->getQuestions();
        } catch (\Throwable $e) {
            $questions = $this->getQuestions();
        }

        return $questions;
    }

    /** @return User[] */
    public function getAllAssistants(): array
    {
        $assistants = [];

        foreach ($this->getGyms() as $gym) {
            foreach ($gym->getUsers() as $gymUser) {
                if ($gymUser->getId() === $this->getId()) {
                    continue;
                }

                $assistants[] = $gymUser;
            }
        }

        return $assistants;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
