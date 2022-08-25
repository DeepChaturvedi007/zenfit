<?php

namespace AppBundle\Controller;
use AppBundle\Entity\MasterMealPlan;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\WorkoutPlanTemplate;
use AppBundle\Entity\Client;
use AppBundle\Entity\WorkoutDay;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/plan")
 */
class PlanController extends Controller
{
    /**
     * @Route("/togglePlanStatus", name="togglePlanStatus")
     * @return JsonResponse
     */
    public function togglePlanStatusAction(Request $request)
    {
        $em = $this->getEm();
        $status = $request->request->get('status');
        $active = $status === WorkoutPlan::STATUS_ACTIVE;
        $id = $request->request->get('id');
        $planType = $request->request->get('planType');

        if($planType === 'workout') {
            $plan = $this->getEm()->find(WorkoutPlan::class,$id);
        } else {
            $plan = $this->getEm()->find(MasterMealPlan::class, $id);
        }

        if ($plan === null) {
            throw new NotFoundHttpException();
        }

        if ($plan instanceof WorkoutPlan) {
            if (in_array($status, [WorkoutPlan::STATUS_ACTIVE, WorkoutPlan::STATUS_INACTIVE, WorkoutPlan::STATUS_HIDDEN], true)) {
                $plan->setStatus($status);
            }
        } elseif ($plan instanceof MasterMealPlan) {
            $plan->setActive($active);
        }

        $em->flush();

        $res = array(
          'status' => $status
        );
        return new JsonResponse($res);
    }
}
