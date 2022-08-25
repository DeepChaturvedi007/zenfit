<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\Event;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\Video;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Enums\ClientImageType;
use AppBundle\Event\ClientMadeChangesEvent;
use AppBundle\Repository\ClientTagRepository;
use AppBundle\Repository\EventRepository;
use ClientBundle\Transformer\ClientTransformer;
use AppBundle\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Intervention\Image\ImageManagerStatic as Image;
use ProgressBundle\Services\ClientProgressHelperService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Entity\User;
use AppBundle\Entity\ClientTag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use VideoBundle\Services\VideoService;

class ClientService
{
    private ClientImageService $clientImageService;
    private EntityManagerInterface $em;
    protected AwsService $aws;
    private QuestionRepository $questionRepository;
    private MailChimpService $mailChimpService;
    private ClientFoodPreferencesService $clientFoodPreferencesService;
    private DocumentService $documentService;
    private WorkoutPlanService $workoutPlanService;
    private MealPlanService $mealPlanService;
    private VideoService $videoService;
    private MeasuringService $measuringService;
    private EventDispatcherInterface $eventDispatcher;
    private ClientProgressHelperService $clientProgressHelperService;
    private string $s3ImagesBucket;
    private string $s3ImagesKeyPrefix;
    private StripeConnectService $stripeConnectService;
    private ErrorHandlerService $errorHandlerService;
    private ClientTransformer $clientTransformer;
    private EventRepository $eventRepository;

    public function __construct(
        DocumentService $documentService,
        VideoService $videoService,
        ClientFoodPreferencesService $clientFoodPreferencesService,
        EntityManagerInterface $em,
        ClientImageService $clientImageService,
        EventRepository $eventRepository,
        AwsService $aws,
        QuestionRepository $questionRepository,
        MailChimpService $mailChimpService,
        MealPlanService $mealPlanService,
        EventDispatcherInterface $eventDispatcher,
        MeasuringService $measuringService,
        ClientProgressHelperService $clientProgressHelperService,
        WorkoutPlanService $workoutPlanService,
        StripeConnectService $stripeConnectService,
        ErrorHandlerService $errorHandlerService,
        ClientTransformer $clientTransformer,
        string $s3ImagesBucket,
        string $s3ImagesKeyPrefix
    ) {
        $this->documentService = $documentService;
        $this->videoService = $videoService;
        $this->em = $em;
        $this->clientImageService = $clientImageService;
        $this->aws = $aws;
        $this->questionRepository = $questionRepository;
        $this->mailChimpService = $mailChimpService;
        $this->clientFoodPreferencesService = $clientFoodPreferencesService;
        $this->mealPlanService = $mealPlanService;
        $this->workoutPlanService = $workoutPlanService;
        $this->eventDispatcher = $eventDispatcher;
        $this->measuringService = $measuringService;
        $this->clientProgressHelperService = $clientProgressHelperService;
        $this->stripeConnectService = $stripeConnectService;
        $this->errorHandlerService = $errorHandlerService;
        $this->clientTransformer = $clientTransformer;
        $this->s3ImagesBucket = $s3ImagesBucket;
        $this->s3ImagesKeyPrefix = $s3ImagesKeyPrefix;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param String $name
     * @param String $email
     * @param User $user
     * @param null $phone
     * @param bool $deleted
     * @return Client
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addClient(string $name, string $email, $user, $phone = null, $deleted = false)
    {
        $client = new Client($user, trim($name), trim($email));
        $client
            ->setPhone($phone)
            ->setDeleted($deleted)
            ->setAcceptTerms(false)
            ->setAcceptEmailNotifications(true)
            ->setToken(bin2hex(random_bytes(32)));

        if ($user->getLanguage() !== null) {
            $client->setLocale($user->getLanguage()->getLocale());
        }

        $this->em->persist($client);
        $this->em->flush();

        $this->mailChimpService->tagLeadAsWonIfItExists($user, $email);

        return $client;
    }

    public function unsubscribeIfActiveSubscription(Client $client): void
    {
        if ($client->getClientStripe()) {
            try {
                $stripeConnectService = $this->stripeConnectService;
                $userStripe = $client->getUser()->getUserStripe();
                if ($userStripe !== null) {
                    $stripeConnectService->setUserStripe($userStripe);
                }

                $stripeConnectService
                    ->setClient($client)
                    ->unsubscribeClient();
            } catch (\Throwable $e) {
                $this->errorHandlerService->captureException($e);
            }
        }
    }

    /** @param int[] $clients */
    public function deleteClients(array $clients): void
    {
        foreach($clients as $client) {
            $entity = $this
                ->em
                ->getRepository(Client::class)
                ->find($client);

            if ($entity !== null) {
                $entity
                    ->setDeleted(true)
                    ->setDeletedAt(new \DateTime('now'));
                $this->unsubscribeIfActiveSubscription($entity);
            }
        }

        $this->em->flush();
    }

    /**
    * @param array $clients
    */
    public function deactivateClients(array $clients)
    {
        foreach($clients as $client) {
            $entity = $this
                ->em
                ->getRepository(Client::class)
                ->find($client);

            if ($entity !== null) {
                $entity
                    ->setActive(false)
                    ->setAccessApp(false);
                $this->unsubscribeIfActiveSubscription($entity);
            }
        }

        $this->em->flush();
    }

    private function assignWorkoutAndMealTemplates(Client $client, User $user, array $tags = [])
    {
        //assign workout templates
        $wts = $this
            ->em
            ->getRepository(WorkoutPlan::class)
            ->getPlansToAutoAssignByUser($user, $tags);

        foreach ($wts as $wt) {
            $this
                ->workoutPlanService
                ->assignPlanToClients($wt, [$client->getId()]);
        }

        //assign meal templates
        $mts = $this
            ->em
            ->getRepository(MasterMealPlan::class)
            ->getPlansToAutoAssignByUser($user, $tags);

        foreach ($mts as $mt) {
            $this
                ->mealPlanService
                ->assignPlanToClients($mt, [$client->getId()]);
        }
    }

    private function assignDocumentsAndVideos(Client $client, User $user, array $tags = [])
    {
        $documents = $this
            ->em
            ->getRepository(Document::class)
            ->getDocumentsToAutoAssignToClientsByUser($user, $tags);

        $videos = $this
            ->em
            ->getRepository(Video::class)
            ->getVideosToAutoAssignToClientsByUser($user, $tags);

        foreach($documents as $document) {
            $this->documentService
                ->createDocumentClientEntity($document, $client);
        }

        foreach($videos as $video) {
            $this->videoService
                ->createVideoClientEntity($video, $client);
        }
    }

    public function addTags(Client $client, $tags = [], $assign = false)
    {
        $tags = $this->isJSON($tags) ? json_decode($tags) : $tags;
        $tags = $tags ? $tags : [];

        if (!empty($tags)) {
            //add client tags
            $clientTags = $client->tagsList();
            $tags = !is_array($tags) ? explode(',', $tags) : $tags;

            foreach ($tags as $tag) {
                if ($tag == '') continue;
                if (!in_array($tag, $clientTags)) {
                    $tagEntity = new ClientTag($client, trim($tag));
                    $client->getTags()->add($tagEntity);
                    $this->em->persist($tagEntity);
                }
            }

            //remove tags
            $tagsToDelete = array_diff($clientTags, $tags);
            /** @var ClientTagRepository $clientTagRepository */
            $clientTagRepository = $this->em->getRepository(ClientTag::class);
            foreach ($tagsToDelete as $tagToDelete) {
                $clientTag = $clientTagRepository->findOneBy([
                    'title' => $tagToDelete,
                    'client' => $client
                ]);

                if ($clientTag !== null) {
                    $client->getTags()->removeElement($clientTag);
                    $this->em->remove($clientTag);
                }
            }
        }

        //if client has just been created
        //we assign docs, videos, plans to the client
        if ($assign) {
            $this->assignWorkoutAndMealTemplates($client, $client->getUser(), $tags);
            $this->assignDocumentsAndVideos($client, $client->getUser(), $tags);
        }
    }

    public function isJSON($string)
    {
       return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
    * @param String $email
    */
    public function clientExistsAtTrainer($email, User $user)
    {
        $client = $this->em->getRepository(Client::class)->findOneBy([
            'email' => $email,
            'user' => $user->getId()
        ]);

        return $client ? $client : false;
    }

    public function clientEmailExist(string $email): bool
    {
        $client = $this->em->getRepository(Client::class)->findOneBy([
            'email' => $email,
            'accessApp' => 1
        ]);

        if ($client) {
            throw new HttpException(422, 'An active client with this e-mail already exists, please use another e-mail or contact support.');
        }

        return false;
    }

    /** @param array<mixed> $body */
    public function submitClientInfo(array $body, Client $client): void
    {
        if (!array_key_exists('measuringSystem', $body) || !array_key_exists($body['measuringSystem'], Client::MEASURING_SYSTEM)) {
            throw new BadRequestHttpException('Wrong measuring system');
        }
        if (!array_key_exists('startWeight', $body)) {
            throw new BadRequestHttpException('Wrong start weight');
        }
        if (array_key_exists('clientFoodPreferences', $body) && !is_array($body['clientFoodPreferences'])) {
            throw new BadRequestHttpException('Bad clientFoodPreferences field provided');
        }

        $measuringSystem = (int) $body['measuringSystem'];
        $startWeight = $body['startWeight'];
        $measuringService = $this->measuringService;
        $clientMeasuringSystem = $client->getMeasuringSystem();
        $startDate = $client->getStartDate();
        $duration = $client->getDuration();

        foreach($body as $key => $val) {
            $val = $this->streamlineValue($val);
            $setter = 'set' . ucfirst($key);

            if ($key === 'questions') {
                foreach ($val as $questionId => $answerValue) {
                    $question = $this->questionRepository->get((int) $questionId);
                    if (is_array($answerValue)) {
                        $answerValue = implode(',', $answerValue);
                    }

                    $question->doAnswer($client, $answerValue);
                }
            }

            if ($key === 'startDate' && $val !== '') {
                if (is_array($val) && $val['date']) {
                    $val = new \DateTime($val['date']);
                } else {
                    $val = new \DateTime($val);
                }
            }

            if ($val == "") {
                $val = null;
            }

            if ($val == "false") {
                $val = false;
            }

            if ($key === 'age' && $val !== null) {
                $val = (int) $val;
            }

            if ($key === 'email' && $val === null) {
                throw new BadRequestHttpException('Please provide email');
            }

            if (method_exists($client, $setter)) {
                $client->$setter($val);
            }
        }

        if ($measuringSystem && $measuringSystem != $clientMeasuringSystem) {
            $measuringService->updateClientBodyProgress($client, $measuringSystem);
        }

        if (array_key_exists('tags', $body)) {
            //add tags to client
            $this->addTags($client, $body['tags']);
        }

        if (array_key_exists('clientFoodPreferences', $body)) {
            $this
                ->clientFoodPreferencesService
                ->setClient($client)
                ->updateClientFoodPreferences($body['clientFoodPreferences'] ? $body['clientFoodPreferences'] : []);
        }

        if (array_key_exists('excludeIngredients', $body)) {
            $this
                ->clientFoodPreferencesService
                ->setClient($client)
                ->updateExcludeIngredients($body['excludeIngredients']);
        }

        //if client doesn't have any body progress measurements
        //we insert the initial one
        if ($client->getLatestBodyProgress() === null && $startWeight) {
             $this
                ->clientProgressHelperService
                ->persistProgressData($client, collect(['weight' => $startWeight]));
        }

        //check if clients duration has changed
        if ($client->getDuration() != $duration || $client->getStartDate() != $startDate) {
            $this->setClientDuration($client->getStartDate(), $client->getDuration(), $client);
        }

        $this->em->flush();
    }

    public function updateClientInformation(Request $request, Client $client, bool $assign = false): void
    {
        $em = $this->em;

        $measuringSystem = $request->request->get('measuringSystem');
        $startWeight = $request->request->get('startWeight');
        $startFat = $request->request->get('startFat');
        $goalType = $request->request->get('goalType');
        $photo = $request->files->get('photo');
        $frontPhoto = $request->files->get('front');
        $sidePhoto = $request->files->get('side');
        $backPhoto = $request->files->get('back');
        $tags = $request->request->get('tags');
        $foodPreferences = $request->request->get('foodPreferences') != '' ? $request->request->get('foodPreferences') : [];
        $excludeIngredients = $request->request->get('excludeIngredients');

        $measuringService = $this->measuringService;
        $clientMeasuringSystem = $client->getMeasuringSystem();

        $startDate = $client->getStartDate();
        $duration = $client->getDuration();

        /**
         * @var string $key
         * @var mixed $val
         */
        foreach ($request->request->all() as $key => $val) {
            if ($key == 'datakey'
                or $key == 'only_survey'
                or $key == 'lead'
                or $key == 'foodPreferences'
                or $key == 'excludeIngredients'
                or $key == 'startFat'
                or $key == 'leadType'
                or $key == 'tags'
                or $key == 'dialog'
                or $key == 'message') {
                continue;
            }

            if ($key === 'questions') {
                /** @var array<string>|string|int $answerValue */
                foreach ($val as $questionId => $answerValue) {
                    $question = $this->questionRepository->get((int) $questionId);
                    if (is_array($answerValue)) {
                        $answerValue = implode(',', $answerValue);
                    }

                    $question->doAnswer($client, (string) $answerValue);
                }
            }

            if ($key == 'startDate' && $val != "") {
                $val = new \DateTime($val);
            }

            if ($val == "") {
                $val = null;
            }

            if ($val == "false") {
                $val = false;
            }

            $val = $this->streamlineValue($val);
            $setter = 'set' . ucfirst((string) $key);

            if (method_exists($client, $setter)) {
                $client->$setter($val);
            }
        }

        if ($measuringSystem && $measuringSystem != $clientMeasuringSystem) {
            $measuringService->updateClientBodyProgress($client, $measuringSystem);
            $em->flush();
        }

        //add tags to client
        $this->addTags($client, $tags, $assign);

        //add foodPreferences to client + excludeIngredients
        if (!is_array($foodPreferences)) {
            $foodPreferences = explode('-', $foodPreferences);
        }

        $this
            ->clientFoodPreferencesService
            ->setClient($client)
            ->updateClientFoodPreferences($foodPreferences)
            ->updateExcludeIngredients($excludeIngredients);

        //add photo to client
        $photoName = $photo ? $this->uploadClientPhoto($photo, $client) : $client->getPhoto();
        $client->setPhoto($photoName);

        /** @var ClientImageService $clientImageService */
        $clientImageService = $this->clientImageService;

        //add client's front photo
        if($frontPhoto) {
            $clientImageService->upload($frontPhoto, new \DateTime(), $client, ClientImageType::FRONT);
        }
        //add client's back photo
        if($backPhoto) {
            $clientImageService->upload($backPhoto, new \DateTime(), $client, ClientImageType::REAR);
        }
        //add client's side photo
        if($sidePhoto) {
            $clientImageService->upload($sidePhoto, new \DateTime(), $client, ClientImageType::SIDE);
        }

        //if client doesn't have any body progress measurements
        //we insert the initial one
        if ($client->getLatestBodyProgress() === null && ($startWeight || $startFat)) {
            $clientProgressHelperService = $this
                ->clientProgressHelperService
                ->persistProgressData($client, collect(['weight' => $startWeight, 'fat' => $startFat]));
        }

        //check if clients duration has changed
        $clientStartDate = $client->getStartDate();
        if ($client->getDuration() != $duration || $client->getStartDate() != $startDate) {
            $this->setClientDuration($client->getStartDate(), $client->getDuration(), $client);
        }

        $em->flush();
    }

    /**
     * @return Client
     * @param int $day
     * @param Client $client
     */
    public function setClientTrackProgressDay($day, $client)
    {
        $client->setDayTrackProgress($day);
        return $client;
    }

    /**
     * @param Client $client
     * @param array $events
     */
    public function dispatchClientEvents(Client $client, $events)
    {
        $dispatcher = $this->eventDispatcher;
        foreach ($events as $eventName) {
            $event = new ClientMadeChangesEvent($client, $eventName);
            $dispatcher->dispatch($event, $eventName);
        }
    }

    public function setClientDuration(?\DateTime $start, ?int $duration, Client $client): Client
    {
        if ($duration === null) {
            $duration = 0;
        }

        $dispatcher = $this->eventDispatcher;
        $event = new ClientMadeChangesEvent($client, Event::TRAINER_EXTENDED_CLIENT);
        $dispatcher->dispatch($event, Event::TRAINER_EXTENDED_CLIENT);

        $client
            ->setStartDate($start)
            ->setDuration($duration);

        $start = $start ?: new \DateTime();

        $start = new \DateTime($start->format('Y-m-d'));
        $endDate = $duration === 0 ? null : $start->modify("+$duration month");
        $client->setEndDate($endDate);

        return $client;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function streamlineValue($value)
    {
        if(is_string($value)) {
            return str_replace(",", ".", $value);
        } else if(is_numeric($value)) {
            return (float)$value;
        } else {
            return $value;
        }
    }

    public function uploadClientPhoto(UploadedFile $file, Client $client): string
    {
        $s3 = $this->aws->getClient();
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $name = md5($client->getId() . $client->getEmail()) . '.' . $ext;

        $image = Image::make($file)
            ->orientate()
            ->fit(256, 256, function ($constraint) {
                $constraint->upsize();
            })
            ->encode($ext, 85);

        $s3->putObject([
            'Bucket' => $this->getBucket(),
            'Key' => $this->getObjectKey($name),
            'Body' => $image->encoded,
            'ContentType' => mime_content_type($file->getPathname())
        ]);

        return $name;
    }

    public function getBucket(): string
    {
        return $this->s3ImagesBucket;
    }

    public function getObjectKeyPrefix(): string
    {
        return $this->s3ImagesKeyPrefix;
    }

    public function getObjectKey(string $key): string
    {
        return $this->getObjectKeyPrefix() . Client::AWS_PHOTO_KEY . '/' . $key;
    }

    public function getKcalNeed(Client $client)
    {
        $missingInput = $this->getEstimateCaloriesNeedString($client);
        if(!$missingInput) {
          $calories = $this->getBmrCalc($client);
          $calories = $this->getFinalCalories($client->getPrimaryGoal(), $calories);
          $calories = number_format($calories, 0, '.', '');
          return $calories;
        }

        return 0;
    }

  	/**
  	 * @param $client
  	 * @return string
  	 */
  	public function getEstimateCaloriesNeedString($client)
  	{
  		$genderString = '';
  		$gender = $client->getGender();
  		$ageString = '';
  		$age = $client->getAge();
  		$heightString = '';
  		$height = $client->getHeight();
  		$startWeightString = '';
  		$startWeight = $client->getStartWeight();
  		$primaryGoalString = '';
  		$primaryGoal = $client->getPrimaryGoal();
  		$activityLevelString = '';
  		$activityLevel = $client->getActivityLevel();

  		if (!$gender) {
  			$genderString = 'Input Gender';
  		}
  		if (!$age) {
  			if (!$gender && $height && $startWeight && $primaryGoal && $activityLevel) {
  				$ageString .= ' and';
  			} else if($gender) {
  				$ageString .= 'Input';
  			} else {
  				$ageString .= ',';
  			}
  			$ageString .= ' Age';
  		}
  		if (!$height) {
  			if ((!$gender || !$age) && $startWeight && $primaryGoal && $activityLevel) {
  				$heightString .= ' and';
  			} else if($gender && $age) {
  				$heightString .= 'Input';
  			} else {
  				$heightString .= ',';
  			}
  			$heightString .= ' Height';
  		}
  		if (!$startWeight) {
  			if ((!$gender || !$age || !$height) && $primaryGoal && $activityLevel) {
  				$startWeightString .= ' and';
  			} else if($gender && $age && $height) {
  				$startWeightString .= 'Input';
  			} else {
  				$startWeightString .= ',';
  			}
  			$startWeightString .= ' Current Weight';
  		}
  		if (!$primaryGoal) {
  			if ((!$gender || !$age || !$height || !$startWeight ) && $activityLevel) {
  				$primaryGoalString .= ' and';
  			} else if($gender && $age && $height && $startWeight) {
  				$primaryGoalString .= 'Input';
  			} else {
  				$startWeightString .= ',';
  			}
  			$primaryGoalString .= ' Primary Goal';
  		}
  		if (!$activityLevel) {
  			if (!$gender || !$age || !$height || !$startWeight || !$primaryGoal) {
  				$activityLevelString .= ' and';
  			} else if($gender && $age && $height && $startWeight && $primaryGoal) {
  				$primaryGoalString .= 'Input';
  			}
  			$activityLevelString .= ' Activity Level';
  		}
  		return $genderString . $ageString . $heightString . $startWeightString . $primaryGoalString . $activityLevelString;
  	}

  	/**
  	 * @param $client
  	 * @return float|int|null
  	 */
  	public function getBmrCalc($client)
  	{
  		$gender = $client->getGender();
  		$age = $client->getAge();
  		$height = $client->getHeight();

  		$startWeight = $client->getMeasuringSystem() == Client::MEASURING_SYSTEM_IMPERIAL
        ? $client->getStartWeight() * Client::MEASURING_SYSTEM_COEFICIENT
        : $client->getStartWeight();

  		$activityLevel = $client->getActivityLevel();
  		$calories = null;
  		$bmr = null;
  		if ( $gender == Client::GENDER_FEMALE ) {
  			$bmr = 10 * $startWeight + 6.25 * $height - 5 * $age - 161;
  		} else {
  			$bmr = 10 * $startWeight + 6.25 * $height - 5 * $age + 5;
  		}

  		if ($activityLevel == 1) {
  			$calories = $bmr * 1.2;
  		} else if ($activityLevel == 2) {
  			$calories = $bmr * 1.374874372;
  		} else if ($activityLevel == 3) {
  			$calories = $bmr * 1.550251256;
  		} else if ($activityLevel == 4) {
  			$calories = $bmr * 1.725125628;
  		} else if ($activityLevel == 5) {
  			$calories = $bmr * 1.9;
  		} else {
        $calories = $bmr;
      }

  		return $calories;
  	}

  	public function getFinalCalories($primaryGoal, $calories)
  	{
  		return match ($primaryGoal) {
        1 => $calories - 1000,
        2 => $calories - 500,
        4 => $calories + 500,
        5 => $calories + 1000,
        default => $calories,
    };
  	}

  	public function getClientsQueryBuilder(User $user): QueryBuilder
  	{
  		return $this->em->getRepository(Client::class)
  			->createQueryBuilder('c')
  			->where('c.user = :user')
  			->andWhere('c.deleted = 0')
  			->setParameter('user', $user);
  	}

  	/**
  	 * @param \Doctrine\ORM\QueryBuilder $qb
  	 * @param string $status
  	 * @param int $q
     * @param array $tags
  	 * @param int $offset
  	 * @param int $limit
  	 *
  	 * @return mixed
  	 */
    public function getClientsQuery($qb, $status, $q, array $tags = [], $offset = 0, $limit = 15)
    {
        $qb->andWhere('c.active = :active')
            ->setParameter('active', $status === 'active' ? true : false);

        if (count($tags) > 0) {
            $qb
                ->leftJoin('c.tags', 'ct')
                ->andWhere($qb->expr()->in('ct.title', $tags));
        }

        if (!empty($q)) {
            $qb->andWhere('c.name like :query')
                ->setParameter('query', '%' . $q . '%');
        }

        $result = $qb->addOrderBy('c.demoClient','DESC')
			->addOrderBy('c.name','ASC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->getQuery()
			->getResult();

	    return $result;

    }

    /** @return array<mixed> */
    public function getClientFilters($filter): array
    {
        return match ($filter) {
            'pending' => [
                $this->eventRepository->findOneByName(Event::NEED_WELCOME),
            ],
            'need-plans' => [
                $this->eventRepository->findOneByName(Event::TRAINER_UPDATE_WORKOUT_PLAN),
                $this->eventRepository->findOneByName(Event::TRAINER_UPDATE_MEAL_PLAN),
            ],
            'progress' => [
                $this->eventRepository->findOneByName(Event::UPDATED_BODYPROGRESS),
                $this->eventRepository->findOneByName(Event::UPLOADED_IMAGE),
            ],
            'missing-checkin' => [
                $this->eventRepository->findOneByName(Event::MISSING_CHECKIN),
            ],
            'unanswered' => [
                $this->eventRepository->findOneByName(Event::SENT_MESSAGE),
            ],
            'old-chats' => [
                $this->eventRepository->findOneByName(Event::MISSING_COMMUNICATION),
            ],
            'ending' => [
                $this->eventRepository->findOneByName(Event::ENDING_SOON),
                $this->eventRepository->findOneByName(Event::COMPLETED),
                $this->eventRepository->findOneByName(Event::SUBSCRIPTION_CANCELED),
            ],
            'payments' => [
                $this->eventRepository->findOneByName(Event::PAYMENT_FAILED),
            ],
            'custom' => [
                $this->eventRepository->findOneByName(Event::CLIENT_REMINDERS_UNRESOLVED),
            ],
            default => [],
        };
    }

    /**
     * @param array<Client> $clients
     * @param array<mixed> $clientsStats
     * @return array<mixed>
     */
    public function collectClients(array $clients, array $clientsStats): array
    {
        $clients = collect($clients)
            ->map(function (Client $client) {
                return $this->clientTransformer->transformForList($client);
            })->toArray();

        foreach ($clients as $key => $client) {
            $clients[$key]['messages']['id'] = null;
            $clients[$key]['messages']['lastMessageSent'] = null;
            $clients[$key]['messages']['oldestUnreadMessage'] = null;
            $clients[$key]['messages']['unreadCount'] = 0;
            $clients[$key]['documents_count'] = 0;
            $clients[$key]['videos_count'] = 0;
            $clients[$key]['workout_plans_count'] = 0;
            $clients[$key]['master_meal_plans_count'] = 0;
            $clients[$key]['previous_kcals'] = null;
            foreach ($clientsStats['messages']['ids'] as $item) {
                if (!is_array($item) ||
                    !array_key_exists('client_id', $item) ||
                    !array_key_exists('id', $client) ||
                    !is_scalar($item['client_id']) ||
                    !is_scalar($client['id']))
                {
                    throw new \RuntimeException();
                }
                if ((int)$item['client_id'] === (int)$client['id']) {
                    $clients[$key]['messages']['id'] = $item['conversation_id'];
                }
            }
            foreach ($clientsStats['messages']['latestMessagesSentDates'] as $item) {
                if ((int)$item['client_id'] === (int)$client['id']) {
                    $clients[$key]['messages']['lastMessageSent'] = $item['data'];
                }
            }
            foreach ($clientsStats['messages']['oldestUnreadMessagesDates'] as $item) {
                if ((int)$item['client_id'] === (int)$client['id']) {
                    $clients[$key]['messages']['oldestUnreadMessage'] = $item['data'];
                }
            }
            foreach ($clientsStats['messages']['unreadCounts'] as $item) {
                if ((int)$item['client_id'] === (int)$client['id']) {
                    $clients[$key]['messages']['unreadCount'] = $item['data'];
                }
            }
            foreach ($clientsStats['messages']['unansweredCounts'] as $item) {
                if ((int)$item['client_id'] === (int)$client['id']) {
                    $clients[$key]['messages']['unansweredCount'] = $item['data'];
                }
            }
            foreach ($clientsStats['documents'] as $item) {
                if ((int) $item['client_id'] === (int)$client['id']) {
                    $clients[$key]['documents_count'] = $item['count'];
                }
            }
            foreach ($clientsStats['videos'] as $item) {
                if ((int) $item['client_id'] === (int)$client['id']) {
                    $clients[$key]['videos_count'] = $item['count'];
                }
            }
            foreach ($clientsStats['workout_plans'] as $item) {
                if ((int) $item['client_id'] === (int)$client['id']) {
                    $clients[$key]['workout_plans_count'] = $item['count'];
                }
            }
            foreach ($clientsStats['master_meal_plans'] as $item) {
                if ((int) $item['client_id'] === (int)$client['id']) {
                    $clients[$key]['master_meal_plans_count'] = $item['count'];
                }
            }
            foreach ($clientsStats['previous_kcals'] as $item) {
                if ((int) $item['client_id'] === (int)$client['id']) {
                    $clients[$key]['previous_kcals'] = $item['kcals'];
                }
            }
        }

        return $clients;
    }

    public function anonymizeClient(Client $client): void
    {
        $client->setName(uniqid());
        $client->setEmail(uniqid().'@anonzenfit.com');
        $client->setToken(null);
        $client->setPassword(uniqid());
        if (!$client->getDeleted()) {
            $client->setDeletedAt(new \DateTime());
            $client->setDeleted(true);
        }
        $phone = $client->getPhone();
        if ($phone !== null) {
            $client->setPhone((string) random_int(0, 99999));
        }

        //delete conversations + messages
        foreach($client->getConversations() as $conversation) {
            $this->em->remove($conversation);
        }

        //delete images
        foreach($client->getImages() as $image) {
            $this->em->remove($image);
        }
    }
}
