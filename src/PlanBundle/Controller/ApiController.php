<?php

namespace PlanBundle\Controller;

use AppBundle\Controller\Controller as Controller;
use AppBundle\Entity\Plan;
use AppBundle\Entity\User;
use PlanBundle\Transformer\PlansTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Method({"GET"})
     * @Route("/plans")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        try {
            $query      = $request->query;
            $limit      = $query->getInt('limit', 10);
            $offset     = $query->getInt('offset', 0);

            $repository = $this->getEm()->getRepository(Plan::class);
            $plans = $repository->findAllPlansByUser($user, $limit, $offset);
            $transformer = new PlansTransformer(collect($plans));
            return new JsonResponse($transformer->getTransformedCollection());
        }
        catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode() ? $e->getCode() : 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'A Server Error Occurred.',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Method({"DELETE"})
     * @Route("/plans/{plan}")
     *
     * @param Request $request
     * @param Plan $plan
     * @return JsonResponse
     */
    public function deleteAction(Request $request, Plan $plan)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if(!$plan) {
            return new JsonResponse([
                'message' => 'Not found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        if(!$this->hasPermission($plan, $user)) {
            return new JsonResponse([
                'message' => 'Permission denied!',
            ], JsonResponse::HTTP_FORBIDDEN);
        }
        try {
            $plan->setDeleted(true);
            $em = $this->getEm();
            $em->persist($plan);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode() ? $e->getCode() : 422);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'A Server Error Occurred.',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param User|null $user
     * @param Plan $plan
     * @return bool
     */
    protected function hasPermission (Plan $plan, User $user = null)
    {
        if(!$user) {
            return false;
        }
        $relatedBundle = $plan->getBundle();
        return $user->getId() === $relatedBundle->getUser()->getId() || !$user->isSuperAdmin();
    }
}
