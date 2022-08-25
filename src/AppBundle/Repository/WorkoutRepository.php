<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Exercise;
use AppBundle\Entity\WorkoutDay;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\Workout;
use Doctrine\ORM\Query\Expr\Join;
use AppBundle\Entity\Workout as Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Entity>
 */
class WorkoutRepository extends ServiceEntityRepository
{
    /** @var class-string<Entity> */
    protected $_entityName = Entity::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    public function getByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('w');

        return $qb
            ->where($qb->expr()->in('w.id', $ids))
            ->getQuery()
            ->getResult();
    }

    public function getByWorkoutDayIds(array $ids, $skipChildren = true)
    {
        $qb = $this->createQueryBuilder('w');

        $qb
            ->leftJoin('w.exercise', 'we', Join::WITH, 'we.id = w.exercise')
            ->leftJoin('we.muscleGroup', 'mb', Join::WITH, 'mb.id = we.muscleGroup')
            ->leftJoin('we.workoutType', 'wt', Join::WITH, 'wt.id = we.workoutType')
            ->leftJoin('we.equipment', 'weq', Join::WITH, 'weq.id = we.equipment')
            ->where($qb->expr()->in('w.workoutDay', ':ids'));

        if ($skipChildren) {
            $qb->andWhere('w.parent is NULL');
        }

        $qb->setParameter('ids', $ids);

        return $qb
            ->orderBy('w.orderBy', 'asc')
            ->getQuery()
            ->getResult();
    }

    /** @return array<mixed> */
    public function saveWorkoutHelper($items, WorkoutDay $dayEntity): array
    {
        $workouts = [];
        $workoutArray = [];
        $em = $this->getEntityManager();

        foreach ($items as $exercise) {
            $exercise_id = $exercise['id'];
            $order = $exercise['order'];
            $workoutId = $exercise['workout_id'];

            if (isset($exercise['comment']) && $exercise['comment'] != "") {
                $comment = $exercise['comment'];
            } else {
                $comment = null;
            }
            if (isset($exercise['info']) && $exercise['info'] != "") {
                $info = $exercise['info'];
            } else {
                $info = null;
            }
            if (isset($exercise['reps']) && $exercise['reps'] != "") {
                $reps = $exercise['reps'];
            } else {
                $reps = null;
            }
            if (isset($exercise['sets']) && $exercise['sets'] != "") {
                $sets = $exercise['sets'];
            } else {
                $sets = null;
            }
            if (isset($exercise['rest']) && $exercise['rest'] != "") {
                $rest = $exercise['rest'];
            } else {
                $rest = null;
            }
            if (isset($exercise['time']) && $exercise['time'] != "") {
                $time = $exercise['time'];
            } else {
                $time = null;
            }
            if (isset($exercise['rm']) && $exercise['rm'] != "") {
                $rm = $exercise['rm'];
            } else {
                $rm = null;
            }
            if (isset($exercise['tempo']) && $exercise['tempo'] != "") {
                $tempo = $exercise['tempo'];
            } else {
                $tempo = null;
            }
            if (isset($exercise['start_weight']) && $exercise['start_weight'] != "") {
                $startWeight = $exercise['start_weight'];
            } else {
                $startWeight = null;
            }
            $workout = null;
            if ($workoutId != null || $workoutId != "") {
                $workout = $em->getRepository(Entity::class)->find($workoutId);
                if ($workout === null) {
                    $workout = new Workout($dayEntity);
                }

            } else {
                $workout = new Workout($dayEntity);
            }

            $_exercise = $em->getReference(Exercise::class, $exercise_id);
            if ($_exercise === null) {
                throw new \RuntimeException();
            }
            $workout->setExercise($_exercise);

            $workout->setWorkoutDay($dayEntity);

            $workout->setInfo($info);
            $workout->setComment($comment);
            $workout->setOrderBy($order);
            $workout->setParent(null);
            $workout->setReps($reps);
            $workout->setRest($rest);
            $workout->setSets($sets);
            $workout->setTime($time);
            $workout->setRm($rm);
            $workout->setTempo($tempo);
            $workout->setStartWeight($startWeight);

            $em->persist($workout);
            $em->flush();
            $workoutArray[] = $workout->getId();

            $subWorkouts = [];

            if (isset($exercise['superset'])) {
                foreach ($exercise['superset'] as $subExercise) {
                    $subExerciseId = $subExercise['id'];
                    $subOrder = $subExercise['order'];
                    $subWorkoutId = $subExercise["workout_id"];
                    $subWorkout = null;
                    if (isset($subExercise['comment']) && $subExercise['comment'] != "") {
                        $subComment = $subExercise['comment'];
                    } else {
                        $subComment = null;
                    }
                    if (isset($subExercise['info']) && $subExercise['info'] != "") {
                        $subInfo = $subExercise['info'];
                    } else {
                        $subInfo = null;
                    }
                    if (isset($subExercise['reps']) && $subExercise['reps'] != "") {
                        $s_reps = $subExercise['reps'];
                    } else {
                        $s_reps = null;
                    }
                    if (isset($subExercise['sets']) && $subExercise['sets'] != "") {
                        $s_sets = $subExercise['sets'];
                    } else {
                        $s_sets = null;
                    }
                    if (isset($subExercise['rest']) && $subExercise['rest'] != "") {
                        $s_rest = $subExercise['rest'];
                    } else {
                        $s_rest = null;
                    }
                    if (isset($subExercise['time']) && $subExercise['time'] != "") {
                        $s_time = $subExercise['time'];
                    } else {
                        $s_time = null;
                    }
                    if (isset($subExercise['rm']) && $subExercise['rm'] != "") {
                        $s_rm = $subExercise['rm'];
                    } else {
                        $s_rm = null;
                    }
                    if (isset($subExercise['tempo']) && $subExercise['tempo'] != "") {
                        $s_tempo = $subExercise['tempo'];
                    } else {
                        $s_tempo = null;
                    }
                    if (isset($subExercise['start_weight']) && $subExercise['start_weight'] != "") {
                        $s_startWeight = $subExercise['start_weight'];
                    } else {
                        $s_startWeight = null;
                    }
                    if ($subWorkoutId != null OR $subWorkoutId != "") {
                        $subWorkout = $em->getRepository(Entity::class)->find($subWorkoutId);
                        if ($subWorkout === null) {
                            throw new NotFoundHttpException();
                        }

                    } else {
                        $subWorkout = new Workout($dayEntity);

                        $sub_exercise = $em->getReference(Exercise::class, $subExerciseId);
                        if ($sub_exercise === null) {
                            throw new \RuntimeException();
                        }
                        $subWorkout->setExercise($sub_exercise);
                    }

                    $subWorkout->setParent($workout);
                    $subWorkout->setInfo($subInfo);
                    $subWorkout->setComment($subComment);
                    $subWorkout->setOrderBy($subOrder);
                    $subWorkout->setWorkoutDay($dayEntity);
                    $subWorkout->setSets($s_sets);
                    $subWorkout->setReps($s_reps);
                    $subWorkout->setRest($s_rest);
                    $subWorkout->setTime($s_time);
                    $subWorkout->setRm($s_rm);
                    $subWorkout->setTempo($s_tempo);
                    $subWorkout->setStartWeight($s_startWeight);

                    $em->persist($subWorkout);
                    $em->flush();
                    $workoutArray[] = $subWorkout->getId();

                    $subWorkouts[] = [
                        "workout_id" => $subWorkout->getId(),
                        "workout_parent_id" => $workout->getId(),
                        "position" => $subWorkout->getOrderBy()

                    ];
                }
            }

            $workouts [] = [
                "workout_day_id" => $dayEntity->getId(),
                "workout_id" => $workout->getId(),
                "position" => $workout->getOrderBy(),
                "sub_workouts" => $subWorkouts
            ];
        }

        $this->removeExercises($workoutArray, $dayEntity);

        $em->flush();

        return $workouts;
    }

    public function removeAllWorkoutDays(WorkoutPlan $plan)
    {
        $em = $this->getEntityManager();

        $workoutDays = $plan->getWorkoutDays();
        foreach ($workoutDays as $workoutDay) {
            $em->remove($workoutDay);
        }

        $em->flush();
    }

    private function removeExercises($workoutArray, WorkoutDay $dayEntity)
    {
        $em = $this->getEntityManager();

        $workouts = $dayEntity->getWorkouts();

        foreach ($workouts as $workout) {
            if (!in_array($workout->getId(), $workoutArray)) {
                $em->remove($workout);
            }
        }

    }
}
