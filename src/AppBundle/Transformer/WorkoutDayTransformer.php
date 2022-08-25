<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\WorkoutDay;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection as ResourceCollection;

class WorkoutDayTransformer extends TransformerAbstract
{
    /**
     * @var Collection
     */
    private $workouts;

    protected $availableIncludes = [
        'workouts',
    ];

    protected $defaultIncludes = [
        'workouts',
    ];

    public function __construct(Collection $workouts)
    {
        $this->workouts = $workouts;
    }

    /**
     * @param WorkoutDay $workoutDay
     * @return array
     */
    public function transform($workoutDay)
    {
        $id = $workoutDay->getId();

        $data = [
            'id' => $id,
            'name' => $workoutDay->getName(),
            'comment' => $workoutDay->getWorkoutDayComment(),
            'order' => $workoutDay->getOrder(),
            'image' => $workoutDay->getImage(),
            'last' => false,
        ];

        return $data;
    }

    /**
     * @param WorkoutDay $workoutDay
     * @return ResourceCollection
     */
    public function includeWorkouts($workoutDay)
    {
        $id = $workoutDay->getId();
        $workouts = $this->workouts->has($id) ? $this->workouts->get($id) : [];

        return $this->collection($workouts, new WorkoutExerciseTransformer);
    }

}
