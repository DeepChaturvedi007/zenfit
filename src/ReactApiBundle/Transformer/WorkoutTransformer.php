<?php

namespace ReactApiBundle\Transformer;

use AppBundle\Entity\Equipment;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\MuscleGroup;
use AppBundle\Entity\Workout;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutType;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Illuminate\Support\Collection as Collect;

const LETTERS = 'abcdefghijklmnopqrstuvwxyz';

class WorkoutTransformer extends TransformerAbstract
{
    /**
     * @var Collect
     */
    private $workouts;

    /**
     * @var Collect
     */
    private $workoutGroups;

    public function __construct(Collect $workouts = null)
    {
        $this->workouts = $workouts ? $workouts : collect();
        $this->workoutGroups = $this
            ->workouts
            ->filter(function (Workout $workout) {
                return $workout->getParent();
            })
            ->groupBy(function (Workout $workout) {
                $parent = $workout->getParent();
                return $parent === null ? null : $parent->getId();
            });
    }

    protected $availableIncludes = [
        'muscle_group',
        'equipment',
        'type',
        'tracking',
    ];

    protected $defaultIncludes = [
        'muscle_group',
        'equipment',
        'type',
    ];

    /**
     * @param Workout $workout
     * @return array
     */
    public function transform(Workout $workout)
    {
        $exercise = $workout->getExercise();
        $parent = $workout->getParent();
        $workoutDay = $workout->getWorkoutDay();
        $startWeight = $workout->getStartWeight();

        $children = $this
            ->workouts
            ->filter(function (Workout $item) use ($workout) {
                return ($parent = $item->getParent()) && $parent->getId() == $workout->getId();
            });

        $comment = $workout->getStartWeight() ?
          strip_tags(br2nl($workout->getComment() ? $workout->getComment() . ". Start Weight: $startWeight" : "Start Weight: $startWeight")) :
          strip_tags(br2nl($workout->getComment()));

        $data = [
            'id' => $workout->getId(),
            'workout_day_id' => $workoutDay->getId(),
            'exercise_id' => $exercise->getId(),
            'parent_id' => $parent ? $parent->getId() : null,
            'name' => $exercise->getName(),
            'comment' => $comment,
            'superset_comment' => $this->getSupersetComment($exercise, $workout, $parent),
            'info' => $workout->getInfo(),
            'preparation' => "",
            'execution' => $exercise->getExecution(),
            'order_by' => $workout->getOrderBy(),
            'time' => $workout->getTime(),
            'reps' => $workout->getReps(),
            'rest' => $workout->getRest(),
            'sets' => $workout->getSets(),
            'start_weight' => $startWeight,
            'tempo' => $workout->getTempo(),
            'rm' => $workout->getRm(),
            'picture_url' => $exercise->getPictureUrl(),
            'video_url' => $exercise->getVideoUrl(),
            'letter' => $this->getLetter($workout, $workoutDay, $parent, $children),
            'children' => $children->count(),
        ];

        return $data;
    }

    /**
     * @param Workout $workout
     * @return Collection
     */
    public function includeTracking(Workout $workout)
    {
        return $this->collection($workout->getTracking(), new TrackWorkoutTransformer);
    }

    public function includeMuscleGroup(Workout $workout): ?Item
    {
        $exercise = $workout->getExercise();
        $muscleGroup = $exercise->getMuscleGroup();
        if ($muscleGroup) {
            return new Item($muscleGroup, function (MuscleGroup $muscleGroup) {
                return [
                    'id' => $muscleGroup->getId(),
                    'name' => $muscleGroup->getName(),
                ];
            });
        }
        return null;
    }

    public function includeEquipment(Workout $workout): ?Item
    {
        $exercise = $workout->getExercise();
        $equipment = $exercise->getEquipment();
        if ($equipment) {
            return new Item($equipment, function (Equipment $equipment) {
                return [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                ];
            });
        }
        return null;
    }

    public function includeType(Workout $workout): ?Item
    {
        $exercise = $workout->getExercise();
        $workoutType = $exercise->getWorkoutType();
        if ($workoutType) {
            return new Item($workoutType, function (WorkoutType $workoutType) {
                return [
                    'id' => $workoutType->getId(),
                    'name' => $workoutType->getName(),
                ];
            });
        }
        return null;
    }

    private function getSupersetComment(Exercise $exercise, Workout $workout, Workout $workoutParent = null): ?string
    {
        $groupItems = $this
            ->workoutGroups
            ->get($workoutParent ? $workoutParent->getId() : $workout->getId());

        if (!$groupItems) {
            return null;
        }

        if ($workoutParent) {
            $workoutIndex = $groupItems->search($workout);
            $groupItems = $groupItems->filter(function (Workout $workout, $index) use ($workoutIndex) {
                return $index > $workoutIndex;
            });
        }

        $nextExercises = $groupItems
            ->map(function (Workout $workout) {
                return $workout->getExercise()->getName();
            });

        $results = 'After performing ' . $exercise->getName() . ', ';

        if ($nextExercises->count()) {
            $results .= 'you should immediately start performing ' . implode(', ', $nextExercises->all()) . '.';
        } else {
            $results .= 'you can start next exercise.';
        }

        return $results;
    }

    private function getLetter(Workout $workout, WorkoutDay $workoutDay, ?Workout $parent, Collect $children): string
    {
        $workouts = $this->workouts
            ->filter(function (Workout $item) use ($workoutDay) {
                return $item->getWorkoutDay() === $workoutDay;
            })
            ->values();

        $suffix = '';

        if ($parent) {
            $index = $workouts->search($parent, true);
            $suffix = $workout->getOrderBy() + 1;
        } else {
            $index = $workouts->search($workout, true);
            if ($children->count()) {
                $suffix = 1;
            }
        }

        return $this->columnLetter($index) . $suffix;
    }

    private function columnLetter(int $c): string
    {
        $letter = '';

        while($c !== 0) {
            $p = ($c - 1) % 26;
            $c = (int) (($c - $p) / 26);
            $letter = chr(65 + $p) . $letter;
        }

        return strtoupper($letter);
    }
}
