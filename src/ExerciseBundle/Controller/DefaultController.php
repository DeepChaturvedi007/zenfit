<?php

namespace ExerciseBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\ExerciseType;
use AppBundle\Entity\MuscleGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutType;
use AppBundle\Repository\ExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/exercises")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/user-exercises", name="user_exercises")
     * @Method({"GET"})
     */
    public function userIndex(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();

        /** @var ExerciseRepository $exerciseRepo */
        $exerciseRepo = $em->getRepository(Exercise::class);

        $exercises = $exerciseRepo->getUserExercises($user->getId(), true);
        $exercises = $exercises->getQuery()->getArrayResult();

        $muscleGroups = $em
            ->getRepository(MuscleGroup::class)
            ->getAllMuscleGroups();

        $equipments = $em
            ->getRepository(Equipment::class)
            ->getAllEquipment();

        $count = count($exercises);
        $demoCount = array_reduce($exercises, function($sum, $exercise) {
            if ((bool) $exercise['demo']) {
                $sum += 1;
            }
            return $sum;
        }, 0);

        $showCreateBox = $count === 0 || $demoCount === $count;

        $view = $this->render("@App/default/user/exercises/index.html.twig",[
            "exercises" => $exercises,
            "muscleGroups" => $muscleGroups,
            "equipments" => $equipments,
            "showCreateBox" => $showCreateBox,
            "q" => $request->query->has("q") ? $request->query->get("q") : ""
        ]);

        return $view;
    }

    /**
     * @Route("/user-exercises")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createUserExercise(Request $request)
    {
        $em = $this->getEm();
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

  	    $body   = $request->getContent();
  	    $result = json_decode( $body );

  	    $muscleGroupId = $result ? $result->muscleGroupId : $request->request->get( 'muscleGroupId' );
  	    $muscleGroup   = $em->getRepository(MuscleGroup::class)->find( $muscleGroupId );

  	    $equipmentId = $result ? $result->equipmentId : $request->request->get( 'equipmentId' );
  	    $equipment   = $em->getRepository(Equipment::class)->find( $equipmentId );

  	    $exerciseTypeId = $result ? $result->exerciseTypeId : $request->request->get( 'exerciseTypeId' );
  	    $type           = $em->getRepository(ExerciseType::class)->find( $exerciseTypeId );

  	    $workoutTypeId = $result ? $result->workoutTypeId : $request->request->get( 'workoutTypeId' );
  	    $workoutType   = $em->getRepository(WorkoutType::class)->find( $workoutTypeId ? $workoutTypeId : 1 );

  	    $name = $result ? $result->name : $request->request->get( "name" );
  	    if ( $name == "" ) {
  		    $response = array(
  			    'msg' => 'The title of the exercise can\'t be empty'
  		    );
  		    return new JsonResponse( $response, 400 );
  	    }

        $execution = $result ? $result->execution : $request->request->get( "execution", '' );

  	    $videoUrl       = $result ? $result->videoUrl : $request->request->get( "videoUrl" );
  	    $videoThumbnail = $result ? $result->videoThumbnail : $request->request->get( "videoThumbnail" );

        $ue = new Exercise($name);
        $ue
            ->setPictureUrl($videoThumbnail)
            ->setVideoUrl($videoUrl)
            ->setUser($user)
            ->setMuscleGroup($muscleGroup)
            ->setEquipment($equipment)
            ->setExerciseType($type)
            ->setWorkoutType($workoutType)
            ->setExecution($execution);

        $em->persist($ue);
        $em->flush();

        $response = array(
            'msg' => 'Success',
            'hash' => $name
        );

        return new JsonResponse($response,201);
    }

    /**
     * @Route("/user-exercises/{id}", name="edit_user_exercise")
     * @Method({"PUT"})
     */
    public function userUpdateAction(Request $request, $id)
    {
        /** @var UploadedFile $file */
        $name = $request->request->get('name');
        $execution = (string) $request->request->get( "execution", '');

        $videoUrl = $request->request->get('videoUrl', null);
        $videoThumbnail = $request->request->get( "videoThumbnail" );
        $wt = $request->request->get('workoutTypeId') ? $request->request->get('workoutTypeId') : 1;

        if($name == "") {
            $response = array(
                'msg' => 'The title of the exercise can\'t be empty'
            );
            return new JsonResponse($response, 400);
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getEm();

        /** @var Exercise $exercise */
        $exercise = $em->getRepository(Exercise::class)->find($id);
        $muscleGroup = $em->getRepository(MuscleGroup::class)->find($request->request->get('muscleGroupId'));
        $equipment = $em->getRepository(Equipment::class)->find($request->request->get('equipmentId'));
        $type = $em->getRepository(ExerciseType::class)->find($request->request->get('exerciseTypeId'));
        $workoutType = $em->getRepository(WorkoutType::class)->find($wt);

        $exercise
            ->setName($name)
            ->setVideoUrl($videoUrl)
            ->setPictureUrl($videoThumbnail)
            ->setMuscleGroup($muscleGroup)
            ->setEquipment($equipment)
            ->setExerciseType($type)
            ->setWorkoutType($workoutType)
            ->setExecution($execution);

        $thumbUrl = (string) $request->request->get("videoThumbnail");

        if ($thumbUrl && $thumbUrl !== $exercise->getPictureUrl()) {
            $exercise->setPictureUrl($thumbUrl);
        }

        $em->persist($exercise);
        $em->flush();

        $response = array(
            'msg' => 'Success',
            'hash' => $request->request->get("name")
        );

        return new JsonResponse($response,201);
    }

}
