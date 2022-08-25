<?php

namespace ExerciseBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Entity\Equipment;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\MuscleGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\Workout;
use AppBundle\Repository\ExerciseRepository;
use Exception;
use IvoPetkov\VideoEmbed;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/api/exercises")
 */
class ApiController extends Controller
{
    /**
     * @param Exercise $exercise
     * @param Workout $workout
     * @return Response
     * @Route("/exerciseInfo/{exercise}/{workout}", name="exerciseInfo", defaults={"workout" = null})
     */
    public function exerciseInfoAction(Exercise $exercise, Workout $workout = null)
    {
        $data = array(
            'id' => $exercise->getId(),
            'name' => $exercise->getName(),
            'video' => $exercise->getVideoUrl(),
        );

        return $this->render('@App/default/exerciseInfo.html.twig', array(
            'workout' => $workout,
            'exercise' => $data
        ));
    }

    /**
     * @param string $videoId
     * @return Response
     * @Route("/youtubeExerciseInfo/{videoId}", name="youtubeExerciseInfo")
     */
    public function youtubeExerciseInfoAction($videoId)
    {
        $error = null;
        $video = null;

        try {
            $video = new VideoEmbed('https://www.youtube.com/watch?v=' . $videoId);
            $video->setSize(538, 399);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return $this->render('@App/default/youtubeExerciseInfo.html.twig', array(
            'video' => $video,
            'error' => $error,
        ));
    }

    /**
    * @Method({"GET"})
    * @Route("")
    * @param Request $request
    * @return JsonResponse
    */
    public function getExercisesAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();

        /** @var ExerciseRepository $repo */
        $repo = $em->getRepository(Exercise::class);

        $search = [];
        if($request->query->has("q")) {
            $search["q"] = $request->query->get('q');
        }
        if($request->query->has("page")) {
            $search["page"] = $request->query->get('page');
        }
        if($request->query->has("limit")) {
            $search["limit"] = $request->query->get('limit');
        }
        if($request->query->has('muscleGroupId')) {
            $search["muscleGroupId"] = $request->query->get('muscleGroupId');
        }
        if($request->query->has('muscleId')) {
            $search["muscleGroupId"] = $request->query->get('muscleId');
        }
        if($request->query->has('equipmentId')) {
            $search["equipmentId"] = $request->query->get('equipmentId');
        }
        if($request->query->has('showOnlyOwn')) {
            $search["showOnlyOwn"] = filter_var($request->query->get('showOnlyOwn'), FILTER_VALIDATE_BOOLEAN);
        }

        $results = $repo->newSearch($search, $user->getId());
        return new JsonResponse($results);
    }

    /**
    * @Method({"GET"})
    * @Route("/equipment")
    * @return JsonResponse
    */
    public function equipmentAction()
    {
        $response = $this
            ->getEm()
            ->getRepository(Equipment::class)
            ->getAllEquipment();

        return new JsonResponse($response);
    }

    /**
    * @Method({"GET"})
    * @Route("/muscle-groups")
    * @return JsonResponse
    */
    public function muscleGroupsAction()
    {
        $response = $this
            ->getEm()
            ->getRepository(MuscleGroup::class)
            ->getAllMuscleGroups();

        return new JsonResponse($response);
    }

    /**
     * @param Exercise $exercise
     * @return Response
     * @throws Exception
     * @Route("/user/exercises/{exercise}", name="remove_exercise")
     * @Method({"DELETE"})
     */
    public function removeExercise(Exercise $exercise)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $em = $this->getEm();

        if($exercise->getUser() !== $user) {
            throw new Exception("This user is not associated with this exercise");
        }

        $exercise->setDeleted(true);
        $em->flush();

        return new Response(null, 204);
    }

    /**
     * @param int $videoId
     * @param string $type
     * @return JsonResponse
     * @Route("/user/exercises/videoThumbnail/{videoId}/{type}", name="exercise_video_thumbnail", defaults={"type" = null})
     */
    public function getVideoThumbnail($videoId, $type = null)
    {
        $em = $this->getEm();
        $exerciseRepo = $em->getRepository(Exercise::class);
        $result = $exerciseRepo->getVideoThumbnail($videoId, $type);

        return new JsonResponse([
            'data' => [
                'url' => $result
            ]
        ]);
    }

}
