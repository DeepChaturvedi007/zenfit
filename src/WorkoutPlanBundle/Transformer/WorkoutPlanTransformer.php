<?php

namespace WorkoutPlanBundle\Transformer;

use AppBundle\Entity\WorkoutPlan;
use League\Fractal\TransformerAbstract;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class WorkoutPlanTransformer extends TransformerAbstract
{
    /** @return array<string, mixed> */
    public function transform(WorkoutPlan $plan): array
    {
        if ($lastUpdated = $plan->getLastUpdated()) {
            $lastUpdated = $lastUpdated->format('Y-m-d H:i:s');
        }

        if ($created = $plan->getCreatedAt()) {
            $created = $created->format('Y-m-d H:i:s');
        }

        $meta = [
            'level' => null,
            'type' => null,
            'location' => null,
            'duration' => null,
            'workoutsPerWeek' => null,
            'gender' => null
        ];

        if ($plan->getWorkoutPlanMeta() !== null) {
            $meta = [
                'level' => $plan->getWorkoutPlanMeta()->getLevel(),
                'type' => $plan->getWorkoutPlanMeta()->getType(),
                'location' => $plan->getWorkoutPlanMeta()->getLocation(),
                'duration' => $plan->getWorkoutPlanMeta()->getDuration(),
                'workoutsPerWeek' => $plan->getWorkoutPlanMeta()->getWorkoutsPerWeek(),
                'gender' => $plan->getWorkoutPlanMeta()->getGender()
            ];
        }

        return [
            'id' => $plan->getId(),
            'name' => $plan->getName(),
            'explaination' => $plan->getExplaination(),
            'comment' => $plan->getComment(),
            'last_updated' => $lastUpdated,
            'created' => $created,
            'days' => $plan->getWorkoutDaysSize(),
            'status' => $plan->getStatus(),
            'active' => $plan->getStatus() === WorkoutPlan::STATUS_ACTIVE ? 1 : 0,
            'meta' => $meta
        ];
    }
}
