<?php

namespace AppBundle\Services;

use AppBundle\Entity\Exercise;
use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutPlanMeta;
use AppBundle\Repository\WorkoutDayRepository;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Repository\WorkoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Collection;
use AppBundle\Entity\Client;
use AppBundle\Entity\WorkoutPlanSettings;
use AppBundle\Entity\User;
use AppBundle\Entity\Event;
use AppBundle\Event\ClientMadeChangesEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WorkoutPlanBundle\Transformer\WorkoutPlanTransformer;

class WorkoutPlanUnauthorized extends \Exception {}

class WorkoutPlanService {

    const TYPE_EDIT = 'edit';
    const TYPE_CLONE = 'clone';
    const TYPE_NEW = 'new';

    private EntityManagerInterface $em;
    private ?WorkoutPlan $plan = null;
    private ?string $name = null;
    private ?string $explaination = null;
    private ?string $comment = null;
    private ?string $status = null;
    private ?int $workoutsPerWeek = null;
    private ?int $duration = null;
    private ?int $gender = null;
    private ?int $location = null;
    private ?int $level = null;
    /** @var array<int, mixed> */
    private array $templates = [];
    private ?WorkoutPlanSettings $settings;
    private ?WorkoutPlanMeta $meta;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setPlan(WorkoutPlan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function setWorkoutPlanMeta(): self
    {
        if ($this->plan === null) {
            throw new \RuntimeException('Plan is not filled in');
        }

        if($this->plan->getWorkoutPlanMeta() === null) {
            $meta = new WorkoutPlanMeta($this->plan);
            $this->em->persist($meta);
            $this->em->flush();
        } else {
            $meta = $this->plan->getWorkoutPlanMeta();
        }

        $this->meta = $meta;
        return $this;
    }

    public function setName(?string $name = null): self
    {
        $this->name = $name;
        if (!$name && $this->plan) {
            $this->name = $this->plan->getName();
        }
        return $this;
    }

    public function setExplaination(?string $explaination = null): self
    {
        $this->explaination = $explaination;
        if (!$explaination && $this->plan) {
            $this->explaination = $this->plan->getExplaination();
        }
        return $this;
    }

    public function setComment(?string $comment = null): self
    {
        $this->comment = $comment;
        if (!$comment && $this->plan) {
            $this->comment = $this->plan->getComment();
        }
        return $this;
    }

    public function setStatus(?string $status = null): self
    {
        $this->status = $status;
        if (!$status && $this->plan) {
            $this->status = $this->plan->getStatus();
        }
        return $this;
    }

    public function setWorkoutsPerWeek(?int $workoutsPerWeek = null): self
    {
        $this->workoutsPerWeek = $workoutsPerWeek;
        if (!$workoutsPerWeek && $this->plan !== null && $this->meta !== null) {
            $this->workoutsPerWeek = $this->meta->getWorkoutsPerWeek();
        }
        return $this;
    }

    public function setLevel(?int $level = null): self
    {
        $this->level = $level;
        if (!$level && $this->plan !== null && $this->meta !== null) {
            $this->level = $this->meta->getLevel();
        }
        return $this;
    }

    public function setDuration(?int $duration = null): self
    {
        $this->duration = $duration;
        if (!$duration && $this->plan !== null && $this->meta !== null) {
            $this->duration = $this->meta->getDuration();
        }
        return $this;
    }

    public function setGender(?int $gender = null): self
    {
        $this->gender = $gender;
        if (!$gender && $this->plan !== null && $this->meta !== null) {
            $this->gender = $this->meta->getGender();
        }
        return $this;
    }

    public function setLocation(?int $location = null): self
    {
        $this->location = $location;
        if (!$location && $this->plan !== null && $this->meta !== null) {
            $this->location = $this->meta->getLocation();
        }
        return $this;
    }

    /** @param array<int, mixed> $templates */
    public function setTemplates(array $templates = []): self
    {
        $this->templates = $templates;
        return $this;
    }

    public function setSettings(?WorkoutPlanSettings $settings = null): self
    {
        $this->settings = $settings;
        return $this;
    }

    /** @param array<string, mixed> $templates */
    public function createPlan(User $user, Client $client = null, WorkoutPlan $plan = null, array $templates = []): WorkoutPlan
    {
        if ($this->name === null) {
            throw new \RuntimeException('Name is not filled in');
        }

        if ($user->isAssistant()) {
            $user = $user->getGymAdmin();
        }

        $isTemplate = !$client;
        $newPlan = $plan ? clone $plan : new WorkoutPlan($this->name);

        $newPlan
            ->setName($this->name)
            ->setComment($this->comment)
            ->setExplaination($this->explaination)
            ->setLastUpdated(new \DateTime())
            ->setCreatedAt(new \DateTime())
            ->setTemplate($isTemplate)
            ->setClient($client)
            ->setUser($user);

        $this->em->persist($newPlan);
        $this->em->flush();

        if (empty($templates) && !$plan) {
            $day = $this->addDay($newPlan, "Day 1", 1);
            $this->em->persist($day);
        }

        //add WorkoutPlanSettings
        $newSettings = null;
        if ($plan) {
            $planSettings = $plan->getSettings();
            if ($planSettings !== null) {
                $newSettings = clone $planSettings;
            }
        }
        if ($newSettings === null) {
            $newSettings = new WorkoutPlanSettings($newPlan);
        }

        $newSettings->setPlan($newPlan);
        $this->em->persist($newSettings);

        //add WorkoutPlanMeta
        $workoutPlanMeta = ($plan && $plan->getWorkoutPlanMeta() !== null) ? clone $plan->getWorkoutPlanMeta() : new WorkoutPlanMeta($newPlan);
        $workoutPlanMeta
            ->setPlan($newPlan)
            ->setWorkoutsPerWeek($this->workoutsPerWeek)
            ->setDuration($this->duration)
            ->setGender($this->gender)
            ->setLevel($this->level)
            ->setLocation($this->location);
        $this->em->persist($workoutPlanMeta);

        if ($client) {
            $client->setWorkoutUpdated(new \DateTime());
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATED_WORKOUT_PLAN);
            $dispatcher->dispatch($event, Event::TRAINER_UPDATED_WORKOUT_PLAN);
        }

        if ($plan) {
            $templates[] = $plan;
        }

        $this->cloneWorkoutPlan($templates, $newPlan);
        $this->em->flush();

        return $newPlan;
    }

    /**
    * @param array<mixed> $clientIds
    * @return array<string, mixed>
    */
  	public function assignPlanToClients(WorkoutPlan $template, array $clientIds): array
    {
        $plans = [];
    		foreach ($clientIds as $clientId) {
    			  $client = $this->em->find(Client::class, $clientId);
      			if ($client !== null) {
                $meta = $template->getWorkoutPlanMeta();

                $plans[] = $this
                    ->setName($template->getName())
                    ->setExplaination($template->getExplaination())
                    ->setComment($template->getComment())
                    ->setWorkoutsPerWeek($meta ? $meta->getWorkoutsPerWeek() : null)
                    ->setDuration($meta ? $meta->getDuration() : null)
                    ->setLocation($meta ? $meta->getLocation() : null)
                    ->setLevel($meta ? $meta->getLevel() : null)
                    ->setGender($meta ? $meta->getGender() : null)
                    ->createPlan($client->getUser(), $client, $template);

      					$client->setWorkoutUpdated(new \DateTime());
      			}
    		}

    		$this->em->flush();

        $collection = collect($plans)
            ->map(function(WorkoutPlan $plan) {
                return (new WorkoutPlanTransformer)->transform($plan);
            });

        return $collection->toArray();
  	}

    public function updatePlan(User $who, WorkoutPlan $plan): void
    {
        if ($this->name === null) {
            throw new \RuntimeException('Name is not filled in');
        }
        if ($this->status === null) {
            throw new \RuntimeException('Status is not filled in');
        }
        if ($this->meta === null) {
            throw new \RuntimeException('Meta is not filled in');
        }
        $plan
            ->setName($this->name)
            ->setComment($this->comment)
            ->setExplaination($this->explaination)
            ->setStatus($this->status);

        $this
            ->meta
            ->setWorkoutsPerWeek($this->workoutsPerWeek)
            ->setDuration($this->duration)
            ->setGender($this->gender)
            ->setLevel($this->level)
            ->setLocation($this->location);

        //--- Apply template to current workout plan
        $templates = $this->getTemplatesByIds($who, $this->templates);
        $this->cloneWorkoutPlan($templates, $plan);

        //--- Update settings for current workout plan
        $this->setPlanSettings($plan);

        $this->em->flush();
    }

    /**
     * @param WorkoutPlan $plan
     * @param array $data
     * @return array
     */
    public function syncPlanDays(WorkoutPlan $plan, array $data)
    {
        $data = collect($data)
            ->map(function($day) {
                $day['day_id'] = preg_replace('/\D/', '', $day['day_id']);
                return $day;
            })
            ->filter(function ($day) {
                return $day['day_id'] > 0;
            })
            ->keyBy('day_id');

        $days = collect($plan->getWorkoutDays()->toArray());
        $removed = $days
            ->map(function (WorkoutDay $day) {
                return $day->getId();
            })
            ->diff($data->keys());

        $response = [];

        foreach ($days as $day) {
            $id = $day->getId();

            if ($removed->contains($id)) {
                $this->em->remove($day);
                continue;
            }

            $item = collect($data->get($id));
            $day->setWorkoutDayComment($item->get('workoutDayComment'));
            $day->setOrder($item->get('order'));
            $this->em->flush();

            if (is_array($item->get('exercises'))) {
                $workouts = $this->syncPlanDayWorkouts($day, $item->get('exercises'));
            } else {
                $workouts = [];
            }

            $response[] = [
                'workout_day_id' => $day->getId(),
                'workouts' => $workouts,
            ];
        }

        $now = new \DateTime();

        if ($client = $plan->getClient()) {
            $client->setWorkoutUpdated($now);
            $dispatcher = $this->eventDispatcher;
            $event = new ClientMadeChangesEvent($client, Event::TRAINER_UPDATED_WORKOUT_PLAN);
            $dispatcher->dispatch($event, Event::TRAINER_UPDATED_WORKOUT_PLAN);
        }

        $plan->setLastUpdated($now);
        $this->em->flush();

        return $response;
    }

    /**
     * @param WorkoutDay $day
     * @param array $data
     * @return array
     */
    public function syncPlanDayWorkouts(WorkoutDay $day, array $data)
    {
        static $stop = null;

        $data = collect($data)
            ->map(function($workout) {
                $workout['superset'] = collect($workout['superset']);
                return collect($workout);
            });

        $workouts = collect($day->getWorkouts()->toArray())
            ->keyBy(function (Workout $workout) {
                return $workout->getId();
            });

        $response = [];

        foreach ($data as $item) {
            $id = $item->get('workout_id');
            $workout = $this->syncPlanDayWorkout($day, $workouts->get($id), $item);

            if ($workouts->has($id)) {
                $workouts->forget($id);
            }

            $subWorkouts = [];

            foreach ($item->get('superset') as $children) {
                $children = collect($children);
                $childrenId = $children->get('workout_id');
                $childrenWorkout = $this->syncPlanDayWorkout($day, $workouts->get($childrenId), $children, $workout);

                if ($workouts->has($childrenId)) {
                    $workouts->forget($childrenId);
                }

                $subWorkouts[] = [
                    'workout_id' => $childrenWorkout->getId(),
                    'workout_parent_id' => $workout->getId(),
                    'position' => $childrenWorkout->getOrderBy(),
                ];
            }

            $response[] = [
                'workout_day_id' => $day->getId(),
                'workout_id' => $workout->getId(),
                'position' => $workout->getOrderBy(),
                'sub_workouts' => $subWorkouts,
            ];
        }

        $workouts->each(function(Workout $workout) {
            $this->em->remove($workout);
        });

        return $response;
    }

    /** @param Collection<mixed> $item */
    public function syncPlanDayWorkout(WorkoutDay $day, ?Workout $workout, Collection $item, ?Workout $parent = null): Workout
    {
        if (!$workout) {
            $workout = new Workout($day);
            $this->em->persist($workout);
        }

        /** @var ?Exercise $exercise */
        $exercise = $this->em->getReference(Exercise::class, $item->get('id'));
        if ($exercise === null) {
            throw new NotFoundHttpException();
        }

        $comment = $item->get('comment') == "null" ? null : $item->get('comment');

        $workout
            ->setInfo($item->get('info'))
            ->setComment($comment)
            ->setOrderBy($item->get('order'))
            ->setReps($item->get('reps'))
            ->setRest($item->get('rest'))
            ->setSets($item->get('sets'))
            ->setTime($item->get('time'))
            ->setRm($item->get('rm'))
            ->setTempo($item->get('tempo'))
            ->setStartWeight($item->get('start_weight'))
            ->setParent($parent)
            ->setExercise($exercise)
            ->setWorkoutDay($day);

        $this->em->flush();

        return $workout;
    }

    /**
     * @param WorkoutPlan $plan
     * @param String $name
     * @param int $order
     * @param String $comment
     * @return WorkoutDay
     */
    public function addDay(WorkoutPlan $plan, $name, $order = 1, $comment = null)
    {
        $day =  new WorkoutDay();

        $day
            ->setWorkoutPlan($plan)
            ->setName($name)
            ->setOrder($order)
            ->setWorkoutDayComment($comment);

        $this->em->persist($day);
        $this->em->flush();

        return $day;
    }

    /** @param array<mixed> $details */
    public function addWorkout(WorkoutDay $day, array $details): Workout
    {
        $workout = new Workout($day);

        $workout
            ->setExercise($details['exercise'])
            ->setInfo($details['info'])
            ->setComment($details['comment'])
            ->setOrderBy($details['orderBy'])
            ->setTime($details['time'])
            ->setReps($details['reps'])
            ->setRest($details['rest'])
            ->setSets($details['sets'])
            ->setStartWeight($details['startWeight']);

        return $workout;
    }

    public function setPlanSettings(WorkoutPlan $plan): void
    {
        if (!$this->settings) {
            return;
        }

        $settings = $plan->getSettings();
        $properties = ['rm', 'tempo', 'weight', 'rest', 'reps', 'sets'];
        $values = collect($this->settings);

        if (!$settings) {
            $settings = new WorkoutPlanSettings($plan);
            $this->em->persist($settings);
        }

        foreach ($properties as $property) {
            $value = (bool) $values->get($property);
            $callable = [$settings, 'set' . ucfirst($property)];
            if (is_callable($callable)) {
                $callable($value);
            }
        }
    }

    /**
     * @param WorkoutPlan $plan
     * @return \Illuminate\Support\Collection
     */
    public function getWorkoutsByPlan($plan)
    {
        $dayIds = array_map(function(WorkoutDay $day) {
            return $day->getId();
        }, $plan->getWorkoutDays()->toArray());

        if (count($dayIds) > 0) {
            /** @var WorkoutRepository $repo */
            $repo = $this->em->getRepository(Workout::class);

            return collect($repo->getByWorkoutDayIds($dayIds))
                ->groupBy(function (Workout $workout) {
                    return $workout->getWorkoutDay()->getId();
                });
        }

        return collect();
    }

    /**
    * @param WorkoutPlan[] $from
    * @param WorkoutPlan $to
    * @return WorkoutPlan
    */
    private function cloneWorkoutPlan(array $from, $to)
    {
        if (count($from) > 0) {
            /** @var WorkoutDayRepository $repo */
            $repo = $this->em
                ->getRepository(WorkoutDay::class);
            $order = (int) $repo
                ->getLastOrderByPlan($to);

            foreach ($from as $plan) {
                /**
                 * @var WorkoutDay[] $days
                 */
                $days = $plan->getWorkoutDays();

                foreach ($days as $day) {
                    $this->cloneDay($day->getName(), $day, $to, ++$order);
                }
            }
        }

        return $to;
    }

    /**
     * @param $name
     * @param WorkoutDay $day
     * @param WorkoutPlan $plan
     * @param int $order
     * @param bool $reorderSiblings
     * @return WorkoutDay
     */
    public function cloneDay($name, WorkoutDay $day, WorkoutPlan $plan, $order = 0, $reorderSiblings = false)
    {
        if (!$order) {
            $order = $day->getOrder() + 1;
        }

        $newDay = clone $day;
        $newDay
            ->setName($name)
            ->setOrder($order)
            ->setWorkoutPlan($plan)
            ->setWorkoutDayComment($day->getWorkoutDayComment());

        $this->em->persist($newDay);

        if ($reorderSiblings) {
            /**
             * @var WorkoutDay[] $planDays
             */
            $planDays = $plan
                ->getWorkoutDays()
                ->toArray();

            foreach ($planDays as $planDay) {
                if ($planDay->getOrder() > $day->getOrder()) {
                    $planDay->setOrder(++$order);
                }
            }
        }

        /**
         * @var Workout[] $workouts
         */
        $workouts = collect($day->getWorkouts()->toArray())
            ->sortBy(function(Workout $item) {
                return $item->getParent() ? 1 : 0;
            })
            ->toArray();

        $workoutsMap = collect();

        foreach ($workouts as $workout) {
            $newWorkout = clone $workout;
            $newWorkout->setWorkoutDay($newDay);

            if ($parent = $workout->getParent()) {
                $newWorkout->setParent($workoutsMap->get($parent->getId()));
            }

            $this->em->persist($newWorkout);
            $workoutsMap->put($workout->getId(), $newWorkout);
        }

        $this->em->flush();

        return $newDay;
    }

    /**
     * @param int[] $ids
     * @return WorkoutPlan[]
     */
    private function getTemplatesByIds(User $user, array $ids): array
    {
        $ids = collect($ids)
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            });

        if ($ids->count() > 0) {
            /** @var WorkoutPlanRepository $repo */
            $repo = $this->em
                ->getRepository(WorkoutPlan::class);
            return $repo
                ->getByIdsAndUser($ids->toArray(), $user, true);
        }

        return [];
    }
}
