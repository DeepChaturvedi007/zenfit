<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Services\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Aws\Exception\AwsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use AppBundle\Consumer\PdfGenerationEvent;
use AppBundle\Services\ErrorHandlerService;

/**
 * @Route("/pdf")
 */
class PDFController extends Controller
{
    private ErrorHandlerService $errorHandlerService;
    private MessageBusInterface $messageBus;

    public function __construct(
        ErrorHandlerService $errorHandlerService,
        MessageBusInterface $messageBus,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->errorHandlerService = $errorHandlerService;
        $this->messageBus = $messageBus;
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/exportPlansPdfWorkout/{workoutPlan}", name="exportPlansPdfWorkout")
     *
     * @param WorkoutPlan $workoutPlan
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function exportWorkoutAction(WorkoutPlan $workoutPlan, Request $request)
    {
        $name = $request->request->get('name', $request->query->get('name'));
        $name = str_replace('/', ',', $name);
        $comment = $request->request->get('comment', $request->query->get('comment', ''));

        try {
            $user = $this->getUser();
            if ($user === null) {
                throw new AccessDeniedHttpException('Please login');
            }

            $this->messageBus->dispatch(
                new PdfGenerationEvent(
                    PdfService::TYPE_WORKOUT,
                    $workoutPlan->getId(),
                    null,
                    $user->getEmail(),
                    PdfService::V1
                )
            );

            return new JsonResponse([
                'msg' => 'You will receive the PDF on email.'
            ]);
        } catch (ProcessFailedException $error) {
            $this->errorHandlerService->captureException($error);
            return new JsonResponse([
                'message' => 'Failed to generate PDF file, try later.',
                'error' => [
                    'message' => $error->getMessage(),
                    'process' => $error->getProcess(),
                    'line' => $error->getLine(),
                ],
            ], 500);
        } catch (AwsException $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/exportPlansPdfMealClient/{masterMealPlan}", name="exportPlansPdfMealClient")
     *
     * @param MasterMealPlan $masterMealPlan
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function exportMealAction(MasterMealPlan $masterMealPlan, Request $request)
    {
        $name = $request->request->get('name', $request->query->get('name'));
        $comment = $request->request->get('comment', $request->query->get('comment', ''));

        try {
            $user = $this->getUser();
            if ($user === null) {
                throw new AccessDeniedHttpException('Please login');
            }

            $masterMealPlanId = $masterMealPlan->getId();
            if ($masterMealPlanId === null) {
                throw new \RuntimeException('Master meal plan does not have an ID yet');
            }

            $this->messageBus->dispatch(
                new PdfGenerationEvent(
                    PdfService::TYPE_MEAL,
                    $masterMealPlanId,
                    null,
                    $user->getEmail(),
                    $masterMealPlan->getVersion()
                )
            );

            return new JsonResponse([
                'msg' => 'You will receive the PDF on email.'
            ]);
        } catch (ProcessFailedException $error) {
            $this->errorHandlerService->captureException($error);
            return new JsonResponse([
                'message' => 'Failed to generate PDF file, try later.',
                'error' => [
                    'message' => $error->getMessage(),
                    'process' => $error->getProcess(),
                    'line' => $error->getLine(),
                ],
            ], 500);
        } catch (AwsException $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
